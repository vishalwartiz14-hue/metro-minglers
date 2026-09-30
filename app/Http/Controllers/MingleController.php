<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMingleRequest;
use App\Models\City;
use App\Models\Interest;
use App\Models\Mingle;
use App\Models\MingleUpdate;
use App\Models\MingleInvite;
use App\Models\UserConnection;
use App\Models\User;
use App\Notifications\MingleInviteNotification;
use App\Notifications\MingleJoinRequestNotification;
use App\Notifications\MingleJoinDecisionNotification;
use App\Notifications\MingleUpdateNotification;
use App\Services\MingleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MingleController extends Controller
{
    public function __construct(
        protected MingleService $mingleService
    ) {
    }

    /**
     * Show the Mingle type selector.
     */
    public function create(): View
    {
        return $this->createForm();
    }

    /**
     * Show the event creation wizard.
     */
    public function createEvent(): View
    {
        return $this->createForm('event');
    }

    /**
     * Show the community creation wizard.
     */
    public function createCommunity(): View{
        return $this->createForm('community');
    }

    /**
     * Load the shared creation view with an optional fixed Mingle type.
     */
    private function createForm(?string $selectedType = null): View{
        $cities          =      City::query()->where('is_active', true)->orderBy('name')->get();
        $categories      =      Interest::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('mingles.create', [
            'user'              =>  auth()->user(),
            'cities'            =>  $cities,
            'categories'        =>  $categories,
            'selectedType'      =>  $selectedType,
        ]);
    }
    /**
     * Store a new Mingle.
     */
    public function store(StoreMingleRequest $request): RedirectResponse{
            $mingle             = $this->mingleService->create(
            user: $request->user(),
            data: $request->validated(),
            image: $request->file('image')
        );
        return redirect()->route('mingles.show', $mingle) ->with( 'status',ucfirst($mingle->type) . ' created successfully.');
    }

    /**
     * Show a Mingle.
     */
    
    public function show(Mingle $mingle): View
    {
        $viewer             = auth()->user();
        $mingle->load(['host', 'cityRecord'])->loadCount('attendees');
        abort_if($mingle->host->deactivated_at, 404);
        abort_if((int) $mingle->user_id !== (int) $viewer->id && $viewer->hasBlockedOrBeenBlockedBy((int) $mingle->user_id), 404);

        $isMember           =   $mingle->attendees()->whereKey($viewer->id)->exists();
        $isPublic           =   in_array($mingle->visibility, ['open', 'public', 'request'], true);
        $isConnectionOnly   =   $mingle->visibility === 'connections' && $viewer->isConnectedWith((int) $mingle->user_id);
        $pendingInvite      =   MingleInvite::query()->where('mingle_id', $mingle->id)->where('user_id', $viewer->id)->where('status', 'pending')->first();
        abort_unless($isPublic || $isMember || $isConnectionOnly || $pendingInvite, 404);

        $blockedIds         =   $viewer->blockedUsers()->pluck('users.id')->merge($viewer->blockedByUsers()->pluck('users.id'))->map(fn ($id) => (int) $id)->all();
        $attendees          =   $mingle->attendees()->whereNull('deactivated_at')->whereNotIn('users.id', $blockedIds)
            ->with('interestOptions:id,name')->orderBy('users.name')->get();
        $mingle->setRelation('attendees', $attendees);
        $mingleUpdates      =   $mingle->updates()->whereNotIn('user_id', $blockedIds)
            ->with('user:id,name,profile_photo_path')->limit(20)->get()->reverse()->values();
        $isRequestPending   =   DB::table('mingle_user')->where('mingle_id', $mingle->id)->where('user_id', $viewer->id)->where('status', 'pending')->exists();
        $pendingJoinRequests = (int) $mingle->user_id === (int) $viewer->id
            ? $mingle->participants()->wherePivot('status', 'pending')->whereNotIn('users.id', $blockedIds)->with('cityRecord')->orderBy('users.name')->get()
            : collect();
        $inviteCandidates   =   collect();
        if ($isMember) {
            $participatingIds   =   $mingle->participants()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
            $alreadyInvitedIds  =   $mingle->invites()->where('status', 'pending')->pluck('user_id')->map(fn ($id) => (int) $id)->all();
            $connections        =   UserConnection::query()->where('status', UserConnection::ACCEPTED)
                ->where(fn ($query) => $query->where('sender_id', $viewer->id)->orWhere('recipient_id', $viewer->id))
                ->with(['sender:id,name,profile_photo_path,deactivated_at', 'recipient:id,name,profile_photo_path,deactivated_at'])->get();
            $inviteCandidates   =   $connections->map(fn (UserConnection $connection) => (int) $connection->sender_id === (int) $viewer->id ? $connection->recipient : $connection->sender)
                ->filter(fn (?User $candidate) => $candidate && ! $candidate->deactivated_at
                    && ! in_array($candidate->id, $blockedIds, true)
                    && ! in_array((int) $candidate->id, $participatingIds, true)
                    && ! in_array((int) $candidate->id, $alreadyInvitedIds, true))
                ->unique('id')->values();
        }
        return view('mingles.show', compact('mingle', 'attendees', 'mingleUpdates', 'isMember', 'pendingInvite', 'isRequestPending', 'pendingJoinRequests', 'inviteCandidates'));
    }

    public function postUpdate(Request $request, Mingle $mingle): RedirectResponse
    {
        abort_unless((int) $mingle->user_id === (int) $request->user()->id, 403);

        $data = $request->validate(['update_body' => ['required', 'string', 'max:1500']]);
        $update = MingleUpdate::create([
            'mingle_id' => $mingle->id,
            'user_id' => $request->user()->id,
            'body' => $data['update_body'],
        ]);

        $host = $request->user();
        $blockedIds = $host->blockedUsers()->pluck('users.id')
            ->merge($host->blockedByUsers()->pluck('users.id'))->map(fn ($id) => (int) $id)->all();
        $mingle->attendees()->where('users.id', '!=', $host->id)->whereNull('users.deactivated_at')
            ->whereNotIn('users.id', $blockedIds)->get()
            ->each(fn (User $recipient) => $recipient->notify(new MingleUpdateNotification($mingle, $update)));

        return to_route('mingles.show', $mingle)->withFragment('updates')->with('status', 'Mingle update shared with members.');
    }

    public function invite(Request $request, Mingle $mingle): RedirectResponse
    {
        abort_unless($mingle->attendees()->whereKey($request->user()->id)->exists(), 403);
        abort_if((int) $mingle->user_id !== (int) $request->user()->id
            && $request->user()->hasBlockedOrBeenBlockedBy((int) $mingle->user_id), 404);
        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $invitee = User::query()->findOrFail($data['user_id']);
        abort_if($invitee->deactivated_at || $invitee->is($request->user()), 404);
        abort_unless($request->user()->isConnectedWith($invitee->id), 403, 'You can invite members you are connected with.');
        abort_if($request->user()->hasBlockedOrBeenBlockedBy($invitee->id), 404);
        abort_if($invitee->hasBlockedOrBeenBlockedBy((int) $mingle->user_id), 404);
        abort_if($mingle->participants()->whereKey($invitee->id)->exists(), 422, 'This member has already joined or requested to join.');

        if (MingleInvite::query()->where('mingle_id', $mingle->id)->where('user_id', $invitee->id)->where('status', 'pending')->exists()) {
            return back()->with('status', 'An invite is already waiting for '.$invitee->name.'.');
        }

        $invite = MingleInvite::query()->updateOrCreate(
            ['mingle_id' => $mingle->id, 'user_id' => $invitee->id],
            ['inviter_id' => $request->user()->id, 'status' => 'pending']
        );
        $invitee->notify(new MingleInviteNotification($mingle, $request->user()->name));

        return back()->with('status', 'Invite sent to '.$invitee->name.'.');
    }

    public function respondToJoinRequest(Request $request, Mingle $mingle, User $member): RedirectResponse
    {
        abort_unless((int) $mingle->user_id === (int) $request->user()->id, 403);
        $data = $request->validate(['action' => ['required', 'in:accept,decline']]);
        $requestRow = DB::table('mingle_user')->where('mingle_id', $mingle->id)->where('user_id', $member->id)->where('status', 'pending')->first();
        abort_unless($requestRow, 404);
        abort_if($member->deactivated_at, 404);
        abort_if($request->user()->hasBlockedOrBeenBlockedBy($member->id), 404);
        abort_if($data['action'] === 'accept' && $mingle->isFull(), 422, 'This Mingle is full.');

        if ($data['action'] === 'accept') {
            DB::table('mingle_user')->where('mingle_id', $mingle->id)->where('user_id', $member->id)->update(['status' => 'accepted', 'updated_at' => now()]);
        } else {
            DB::table('mingle_user')->where('mingle_id', $mingle->id)->where('user_id', $member->id)->delete();
        }

        $member->notify(new MingleJoinDecisionNotification($mingle, $data['action'] === 'accept'));

        return to_route('mingles.show', $mingle)->with('status', $data['action'] === 'accept' ? $member->name.' joined the Mingle.' : 'Join request declined.');
    }

    public function declineInvite(Request $request, MingleInvite $invite): RedirectResponse
    {
        abort_unless((int) $invite->user_id === (int) $request->user()->id, 403);
        abort_unless($invite->status === 'pending', 404);
        $invite->update(['status' => 'declined']);
        return to_route('notifications.index')->with('status', 'Mingle invitation declined.');
    }
}
