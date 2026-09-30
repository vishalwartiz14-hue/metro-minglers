<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Interest;
use App\Models\Mingle;
use App\Models\User;
use App\Models\UserConnection;
use App\Models\MingleInvite;
use App\Notifications\MingleJoinRequestNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiscoverController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'filters_applied' => ['nullable', 'boolean'],
            'interest_ids' => ['nullable', 'array', 'max:14'],
            'interest_ids.*' => ['integer', 'exists:interests,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'distance' => ['nullable', 'integer', 'in:25,50,100,250'],
            'min_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'online' => ['nullable', 'boolean'],
            'type' => ['nullable', 'in:event,community'],
            'date' => ['nullable', 'date', 'before_or_equal:today'],
            'section' => ['nullable', 'in:all,people,mingles'],
            'layout' => ['nullable', 'in:grid,list'],
        ]);
        $validator->after(function ($validator) use ($request): void {
            $min = $request->integer('min_age');
            $max = $request->integer('max_age');
            if ($min && $max && $min > $max) {
                $validator->errors()->add('max_age', 'Maximum age must be the same as or greater than minimum age.');
            }
        });
        $filters = $validator->validate();

        $filtersApplied = $request->boolean('filters_applied');
        $selectedInterestIds = $filtersApplied
            ? array_map('intval', $filters['interest_ids'] ?? [])
            : $user->interestOptions()->pluck('interests.id')->map(fn ($id) => (int) $id)->all();
        $cityId = $filtersApplied ? ($filters['city_id'] ?? null) : $user->city_id;
        $radius = $filters['distance'] ?? null;
        $minAge = $filters['min_age'] ?? null;
        $maxAge = $filters['max_age'] ?? null;
        $onlineOnly = $request->boolean('online');
        $section = $filters['section'] ?? 'all';
        $layout = $filters['layout'] ?? 'grid';
        $blockedIds = $user->blockedUsers()->pluck('users.id')
            ->merge($user->blockedByUsers()->pluck('users.id'))->map(fn ($id) => (int) $id)->all();

        $cityIdsInRange = null;
        if ($radius) {
            $origin = City::query()->find($cityId ?: $user->city_id);
            if ($origin?->latitude !== null && $origin?->longitude !== null) {
                $cityIdsInRange = City::query()->where('is_active', true)
                    ->whereNotNull('latitude')->whereNotNull('longitude')->get()
                    ->filter(fn (City $city) => $this->distanceInMiles(
                        $origin->latitude,
                        $origin->longitude,
                        $city->latitude,
                        $city->longitude
                    ) <= $radius)
                    ->modelKeys();
            }
        }

        $peopleQuery = User::query()
            ->select(['id', 'name', 'city_id', 'date_of_birth', 'occupation', 'profile_photo_path', 'last_active_at'])
            ->with(['cityRecord:id,name', 'interestOptions:id,name'])
            ->whereKeyNot($user->id)
            ->whereNull('deactivated_at')
            ->whereNotIn('id', $blockedIds);

        if ($cityId && ! $radius) {
            $peopleQuery->where('city_id', $cityId);
        }

        if ($cityIdsInRange !== null) {
            $peopleQuery->whereIn('city_id', $cityIdsInRange);
        }

        if ($selectedInterestIds !== []) {
            $peopleQuery->whereHas('interestOptions', function (Builder $interests) use ($selectedInterestIds): void {
                $interests->whereIn('interests.id', $selectedInterestIds)->where('interests.is_active', true);
            });
        }

        if ($minAge) {
            $peopleQuery->whereNotNull('date_of_birth')->whereDate('date_of_birth', '<=', today()->subYears($minAge));
        }

        if ($maxAge) {
            $peopleQuery->whereNotNull('date_of_birth')->whereDate('date_of_birth', '>=', today()->subYears($maxAge + 1)->addDay());
        }

        if ($onlineOnly) {
            $peopleQuery->where('last_active_at', '>=', now()->subMinutes(5));
        }

        $people = $section === 'mingles' ? collect() : $peopleQuery->orderBy('name')->take(24)->get();

        $minglesQuery = Mingle::query()
            ->select(['id', 'user_id', 'type', 'title', 'city_id', 'category', 'starts_at', 'venue', 'visibility', 'maximum_attendees', 'image_path'])
            ->with(['host:id,name,profile_photo_path', 'cityRecord:id,name'])
            ->withCount('attendees')
            ->withExists([
                'attendees as joined_by_current_user' => fn ($query) => $query->where('users.id', $user->id),
                'participants as requested_by_current_user' => fn ($query) => $query->where('users.id', $user->id)->where('mingle_user.status', 'pending'),
            ])
            ->publiclyVisible()
            ->whereHas('host', fn (Builder $host) => $host->whereNull('deactivated_at'))
            ->whereNotIn('user_id', $blockedIds);

        if ($cityId && ! $radius) {
            $minglesQuery->where('city_id', $cityId);
        }

        if ($cityIdsInRange !== null) {
            $minglesQuery->whereIn('city_id', $cityIdsInRange);
        }

        if ($selectedInterestIds !== []) {
            $selectedInterestNames = Interest::query()->whereIn('id', $selectedInterestIds)->where('is_active', true)->pluck('name');
            $minglesQuery->whereIn('category', $selectedInterestNames);
        }

        if (in_array($filters['type'] ?? null, ['event', 'community'], true)) {
            $minglesQuery->where('type', $filters['type']);
        }

        if (! empty($filters['date'])) {
            $minglesQuery->whereDate('starts_at', $filters['date']);
        }

        $minglesQuery->where(fn (Builder $query) => $query->where('type', 'community')->orWhere('starts_at', '>=', now()));
        $mingles = $section === 'people' ? Mingle::query()->whereRaw('1 = 0')->simplePaginate(9) : $minglesQuery->latest('starts_at')->simplePaginate(9);

        $pendingConnections = $user->receivedConnections()->where('status', UserConnection::PENDING)
            ->with('sender:id,name,profile_photo_path,city_id')->latest()->take(8)->get();

        $activeFilterCount = collect([
            $request->filled('city_id'),
            $request->filled('distance'),
            $request->filled('min_age') || $request->filled('max_age'),
            $request->filled('type'),
            $request->filled('date'),
            $request->boolean('online'),
            count($request->input('interest_ids', [])) > 0,
        ])->filter()->count();

        return view('discover.index', [
            'user' => $user,
            'people' => $people,
            'mingles' => $mingles->withQueryString(),
            'cities' => City::query()->where('is_active', true)->orderBy('name')->get(),
            'interests' => Interest::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'selectedInterestIds' => $selectedInterestIds,
            'selectedCityId' => $cityId,
            'radius' => $radius,
            'minAge' => $minAge,
            'maxAge' => $maxAge,
            'onlineOnly' => $onlineOnly,
            'section' => $section,
            'layout' => $layout,
            'pendingConnections' => $pendingConnections,
            'activeFilterCount' => $activeFilterCount,
        ]);
    }

    public function join(Request $request, Mingle $mingle)
    {
        $user = $request->user();
        abort_if($mingle->host()->whereNotNull('deactivated_at')->exists(), 404);
        abort_if((int) $mingle->user_id !== (int) $user->id && $user->hasBlockedOrBeenBlockedBy((int) $mingle->user_id), 404);
        abort_if($mingle->attendees()->whereKey($user->id)->exists(), 422, 'You already joined this Mingle.');

        if ($mingle->isCommunity() && $mingle->visibility === 'request') {
            abort_if($mingle->isFull(), 422, 'This Mingle is full.');
            $existingRequest = DB::table('mingle_user')->where('mingle_id', $mingle->id)->where('user_id', $user->id)->first();
            if ($existingRequest) {
                return back()->with('status', 'Your join request is waiting for the host.');
            }

            DB::table('mingle_user')->insert([
                'mingle_id' => $mingle->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $mingle->host->notify(new MingleJoinRequestNotification($mingle, $user->name));

            return back()->with('status', 'Your request was sent to the community host.');
        }

        $pendingInvite = MingleInvite::query()->where('mingle_id', $mingle->id)->where('user_id', $user->id)->where('status', 'pending')->first();
        $canJoin = $mingle->publiclyVisible()->exists()
            || ($mingle->visibility === 'connections' && $user->isConnectedWith((int) $mingle->user_id))
            || (in_array($mingle->visibility, ['invite', 'private'], true) && $pendingInvite);
        abort_unless($canJoin, 404);
        abort_if($mingle->isFull(), 422, 'This Mingle is full.');

        DB::transaction(function () use ($mingle, $user, $pendingInvite): void {
            $mingle->attendees()->syncWithoutDetaching([$user->id]);
            $pendingInvite?->update(['status' => 'accepted']);
        });

        return back()->with('status', 'You joined '.$mingle->title.'!');
    }

    private function distanceInMiles(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB): float
    {
        $earthRadius = 3958.7613;
        $latA = deg2rad($latitudeA);
        $latB = deg2rad($latitudeB);
        $deltaLat = deg2rad($latitudeB - $latitudeA);
        $deltaLng = deg2rad($longitudeB - $longitudeA);
        $a = sin($deltaLat / 2) ** 2 + cos($latA) * cos($latB) * sin($deltaLng / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }
}
