<?php

namespace App\Http\Controllers;

use App\Models\Mingle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyMinglesController extends Controller
{
    public function index(Request $request): View
    {
        $user           =   $request->user();

        $created        =   Mingle::query()->where('user_id', $user->id)
            ->with('cityRecord:id,name')->withCount('attendees')->latest('created_at')
            ->simplePaginate(6, ['*'], 'created_page')->withQueryString();

        $upcomingRsvps = $user->joinedMingles()->where('mingles.user_id', '!=', $user->id)
            ->where('mingles.type', 'event')->where(function ($query) {
                $query->where('starts_at', '>=', now())
                    ->orWhere(fn ($ongoing) => $ongoing
                        ->where('starts_at', '<', now())
                        ->where('ends_at', '>=', now()));
            })
            ->with(['host:id,name', 'cityRecord:id,name'])
            ->withCount('attendees')
            ->orderBy('starts_at')
            ->simplePaginate(6, ['*'], 'upcoming_page')
            ->withQueryString();

        $pastRsvps = $user->joinedMingles()
            ->where('mingles.user_id', '!=', $user->id)
            ->where('mingles.type', 'event')
            ->where(function ($query) {
                $query->where(fn ($ended) => $ended
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', now()))
                    ->orWhere(fn ($started) => $started
                        ->whereNull('ends_at')
                        ->where('starts_at', '<', now()));
            })
            ->with(['host:id,name', 'cityRecord:id,name'])
            ->withCount('attendees')
            ->latest('starts_at')
            ->simplePaginate(6, ['*'], 'past_page')
            ->withQueryString();

        $communities = $user->joinedMingles()
            ->where('mingles.type', 'community')
            ->with(['host:id,name', 'cityRecord:id,name'])
            ->withCount('attendees')
            ->latest('created_at')
            ->simplePaginate(6, ['*'], 'communities_page')
            ->withQueryString();

        return view('mingles.mine', compact('created', 'upcomingRsvps', 'pastRsvps', 'communities'));
    }

    public function leave(Request $request, Mingle $mingle): RedirectResponse
    {
        abort_if((int) $mingle->user_id === (int) $request->user()->id, 403);
        abort_unless($mingle->attendees()->whereKey($request->user()->id)->exists(), 404);

        $mingle->attendees()->detach($request->user()->id);

        return to_route('my-mingles.index')
            ->with('status', $mingle->isCommunity()
                ? 'You left '.$mingle->title.'.'
                : 'Your RSVP for '.$mingle->title.' was cancelled.');
    }

    public function updateReminder(Request $request, Mingle $mingle): RedirectResponse
    {
        abort_unless($mingle->isEvent(), 404);

        $validated = $request->validate([
            'email_reminders' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        abort_unless($user->joinedMingles()->whereKey($mingle->id)->exists(), 404);

        $user->joinedMingles()->updateExistingPivot($mingle->id, [
            'email_reminders' => (bool) $validated['email_reminders'],
        ]);

        return to_route('my-mingles.index')
            ->with('status', $validated['email_reminders']
                ? 'Email reminders enabled for '.$mingle->title.'.'
                : 'Email reminders disabled for '.$mingle->title.'.');
    }
}
