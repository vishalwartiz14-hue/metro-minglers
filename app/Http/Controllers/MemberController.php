<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserConnection;
use App\Models\UserReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function show(Request $request, User $member): View
    {
        $viewer = $request->user();
        $this->ensureVisible($viewer, $member);

        $connection = UserConnection::query()
            ->where(function (Builder $query) use ($viewer, $member): void {
                $query->where(fn (Builder $pair) => $pair->where('sender_id', $viewer->id)->where('recipient_id', $member->id))
                    ->orWhere(fn (Builder $pair) => $pair->where('sender_id', $member->id)->where('recipient_id', $viewer->id));
            })
            ->orderByRaw("CASE WHEN status = 'accepted' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->first();

        $member->load(['cityRecord', 'interestOptions']);

        return view('members.show', [
            'member' => $member,
            'connection' => $connection,
            'isSelf' => $viewer->is($member),
            'canMessage' => $viewer->isConnectedWith($member->id),
        ]);
    }

    public function connect(Request $request, User $member): RedirectResponse
    {
        $viewer = $request->user();
        $this->ensureVisible($viewer, $member);
        abort_if($viewer->is($member), 404);

        if ($viewer->isConnectedWith($member->id)) {
            return back()->with('status', 'You are already connected with '.$member->name.'.');
        }

        $incoming = UserConnection::query()
            ->where('sender_id', $member->id)
            ->where('recipient_id', $viewer->id)
            ->where('status', UserConnection::PENDING)
            ->first();

        if ($incoming) {
            $incoming->update(['status' => UserConnection::ACCEPTED]);

            return back()->with('status', 'You are now connected with '.$member->name.'.');
        }

        $outgoing = UserConnection::query()->firstOrNew([
            'sender_id' => $viewer->id,
            'recipient_id' => $member->id,
        ]);

        if ($outgoing->status === UserConnection::ACCEPTED) {
            return back()->with('status', 'You are already connected with '.$member->name.'.');
        }

        if ($outgoing->status === UserConnection::PENDING) {
            return back()->with('status', 'Your connection request is waiting for a reply.');
        }

        $outgoing->status = UserConnection::PENDING;
        $outgoing->save();

        return back()->with('status', 'Connection request sent to '.$member->name.'.');
    }

    public function respond(Request $request, UserConnection $connection): RedirectResponse
    {
        abort_unless((int) $connection->recipient_id === (int) $request->user()->id, 403);
        abort_unless($connection->status === UserConnection::PENDING, 404);

        $data = $request->validate(['action' => ['required', Rule::in(['accept', 'decline'])]]);
        $sender = User::query()->findOrFail($connection->sender_id);
        $this->ensureVisible($request->user(), $sender);

        $connection->update([
            'status' => $data['action'] === 'accept' ? UserConnection::ACCEPTED : UserConnection::DECLINED,
        ]);

        return to_route('members.show', $sender)->with(
            'status',
            $data['action'] === 'accept' ? 'Connection accepted.' : 'Connection request declined.'
        );
    }

    public function disconnect(Request $request, User $member): RedirectResponse
    {
        $this->ensureVisible($request->user(), $member);

        UserConnection::query()->where(function (Builder $query) use ($request, $member): void {
            $query->where(fn (Builder $pair) => $pair->where('sender_id', $request->user()->id)->where('recipient_id', $member->id))
                ->orWhere(fn (Builder $pair) => $pair->where('sender_id', $member->id)->where('recipient_id', $request->user()->id));
        })->delete();

        return to_route('members.show', $member)->with('status', 'Connection removed.');
    }

    public function block(Request $request, User $member): RedirectResponse
    {
        abort_if($request->user()->is($member), 404);
        abort_if($member->deactivated_at, 404);

        DB::transaction(function () use ($request, $member): void {
            $request->user()->blockedUsers()->syncWithoutDetaching([$member->id]);
            UserConnection::query()->where(function (Builder $query) use ($request, $member): void {
                $query->where(fn (Builder $pair) => $pair->where('sender_id', $request->user()->id)->where('recipient_id', $member->id))
                    ->orWhere(fn (Builder $pair) => $pair->where('sender_id', $member->id)->where('recipient_id', $request->user()->id));
            })->delete();
        });

        return to_route('discover.index')->with('status', $member->name.' has been blocked.');
    }

    public function unblock(Request $request, User $member): RedirectResponse
    {
        $request->user()->blockedUsers()->detach($member->id);

        return to_route('members.blocked')->with('status', $member->name.' has been unblocked.');
    }

    public function blocked(Request $request): View
    {
        return view('members.blocked', [
            'members' => $request->user()->blockedUsers()->orderBy('name')->get(),
        ]);
    }

    public function report(Request $request, User $member): RedirectResponse
    {
        abort_if($request->user()->is($member), 404);
        abort_if($member->deactivated_at, 404);

        $data = $request->validate([
            'reason' => ['required', Rule::in(['spam', 'harassment', 'inappropriate', 'impersonation', 'other'])],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        UserReport::create([
            'reporter_id' => $request->user()->id,
            'reported_id' => $member->id,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
        ]);

        return to_route('members.show', $member)->with('status', 'Thanks. Your report was sent to the safety team.');
    }

    public function inbox(Request $request): View
    {
        return view('messages.index');
    }

    public function messages(Request $request, User $member): View
    {
        $viewer = $request->user();
        $this->ensureVisible($viewer, $member);
        abort_unless($viewer->isConnectedWith($member->id), 403, 'Connect with this member before sending a message.');

        return view('messages.show', compact('member'));
    }

    private function ensureVisible(User $viewer, User $member): void
    {
        abort_if($member->deactivated_at, 404);

        if (! $viewer->is($member)) {
            abort_if($viewer->hasBlockedOrBeenBlockedBy($member->id), 404);
        }
    }
}
