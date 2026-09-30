<x-app-layout>
    <div class="member-profile-page">
        @if (session('status'))
            <div class="member-toast">{{ session('status') }}</div>
        @endif
        @if ($errors->any())<div class="member-form-error">{{ $errors->first() }}</div>@endif

        <a class="member-profile-back" href="{{ route('discover.index') }}">← Back to Discover</a>
        <article class="member-profile-card">
            <div class="member-profile-cover" @if($member->cover_photo_path) style="background-image:linear-gradient(90deg,#081b32aa,#081b3210),url('{{ asset('storage/'.$member->cover_photo_path) }}')" @endif>
                <span class="member-profile-city">{{ $member->cityRecord?->name ?: 'MetroMinglers member' }}</span>
            </div>
            <div class="member-profile-main">
                <div class="member-profile-avatar">
                    @if ($member->profile_photo_path)
                        <img src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->name }}">
                    @else
                        <span>{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="member-profile-identity">
                    <div class="member-profile-name-row">
                        <div>
                            <h1>{{ $member->name }}@if($member->date_of_birth), {{ $member->date_of_birth->age }}@endif</h1>
                            <p>{{ $member->occupation ?: 'MetroMinglers member' }} <span>·</span> {{ $member->cityRecord?->name ?: 'Your city' }}</p>
                        </div>
                        <span class="member-online {{ $member->isOnline() ? 'is-online' : '' }}"><i></i>{{ $member->isOnline() ? 'Online now' : 'Recently joined' }}</span>
                    </div>
                    <div class="member-profile-actions">
                        @if ($isSelf)
                            <a class="member-action-primary" href="{{ route('profile.edit') }}">Edit profile</a>
                        @elseif ($connection?->status === 'accepted')
                            <form method="POST" action="{{ route('members.disconnect', $member) }}" onsubmit="return confirm('Remove this connection?')">@csrf @method('DELETE')<button class="member-action-secondary" type="submit">Connected · Remove</button></form>
                        @elseif ($connection?->status === 'pending' && (int) $connection->sender_id === (int) auth()->id())
                            <button class="member-action-secondary" type="button" disabled>Request sent</button>
                        @elseif ($connection?->status === 'pending' && (int) $connection->recipient_id === (int) auth()->id())
                            <form method="POST" action="{{ route('connections.respond', $connection) }}">@csrf<input type="hidden" name="action" value="accept"><button class="member-action-primary" type="submit">Accept request</button></form>
                            <form method="POST" action="{{ route('connections.respond', $connection) }}">@csrf<input type="hidden" name="action" value="decline"><button class="member-action-secondary" type="submit">Decline</button></form>
                        @else
                            <form method="POST" action="{{ route('members.connect', $member) }}">@csrf<button class="member-action-primary" type="submit">＋ Connect</button></form>
                        @endif

                        @unless($isSelf)
                            <details class="member-profile-menu">
                                <summary aria-label="More member options">•••</summary>
                                <div class="member-profile-menu__panel">
                                    <form method="POST" action="{{ route('members.block', $member) }}" onsubmit="return confirm('Block this member? They will no longer see or contact you.')">@csrf<button type="submit">Block member</button></form>
                                    <details class="member-report-details">
                                        <summary>Report member</summary>
                                        <form method="POST" action="{{ route('members.report', $member) }}">
                                            @csrf
                                            <label>Reason<select name="reason" required><option value="">Choose a reason</option><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="inappropriate">Inappropriate profile</option><option value="impersonation">Impersonation</option><option value="other">Other</option></select></label>
                                            <label>Details<textarea name="details" rows="3" maxlength="1000" placeholder="Share details that can help our safety team."></textarea></label>
                                            <button type="submit">Send report</button>
                                        </form>
                                    </details>
                                </div>
                            </details>
                        @endunless
                    </div>
                </div>
            </div>

            <div class="member-profile-content">
                <section class="member-about">
                    <p class="member-profile-eyebrow">ABOUT {{ strtoupper($member->name) }}</p>
                    <h2>A little about me</h2>
                    <p>{{ $member->about_me ?: 'This member is still adding to their story. Connect and start a conversation around something you both enjoy.' }}</p>
                    <div class="member-facts">
                        @if ($member->education)<span><b>Education</b>{{ $member->education }}</span>@endif
                        @if ($member->gender)<span><b>Gender</b>{{ str_replace('_', ' ', ucfirst($member->gender)) }}</span>@endif
                        @if ($member->date_of_birth)<span><b>Age</b>{{ $member->date_of_birth->age }}</span>@endif
                    </div>
                </section>
                <section class="member-interests">
                    <p class="member-profile-eyebrow">THINGS WE CAN TALK ABOUT</p>
                    <h2>Interests</h2>
                    <div class="member-interest-tags">
                        @forelse ($member->interestOptions as $interest)
                            <span>{{ $interest->name }}</span>
                        @empty
                            <span>Interests coming soon</span>
                        @endforelse
                    </div>
                </section>
            </div>
        </article>
    </div>
</x-app-layout>
