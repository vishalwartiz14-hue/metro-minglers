<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="live-mingle">
        <div class="live-page-shell">
            @if (session('status'))
                <div class="member-toast">{{ session('status') }}</div>
            @endif

            <a href="{{ route('dashboard') }}" class="live-back-link">&larr; Back to Mingles</a>

            <div class="live-cover" @if($mingle->image_path) style="--live-cover-image:url('{{ asset('storage/'.ltrim($mingle->image_path, '/')) }}')" @endif>
           
            @if($mingle->image_path)
                    <img class="live-cover__photo" src="{{ asset('storage/'.ltrim($mingle->image_path, '/')) }}" alt="{{ $mingle->title }} cover">
                @endif
                <span class="live-type">{{ $mingle->type === 'event' ? 'EVENT MINGLE' : 'COMMUNITY MINGLE' }}</span>
            </div>

            <div class="live-content-grid">
                <article class="live-card">
                    <div class="live-host">
                        <div class="live-avatar">
                            @if($mingle->host->profile_photo_path)
                                <img src="{{ asset('storage/'.$mingle->host->profile_photo_path) }}" alt="{{ $mingle->host->name }}">
                            @else
                                {{ strtoupper(substr($mingle->host->name, 0, 1)) }}
                            @endif
                        </div>
                        <span>Hosted by <b><a href="{{ route('members.show', $mingle->host) }}">{{ $mingle->host->name }}</a></b></span>
                    </div>

                    <p class="live-eyebrow">{{ $mingle->category ?: ($mingle->type === 'event' ? 'LOCAL EVENT' : 'LOCAL COMMUNITY') }}</p>
                    <b><h2>{{ $mingle->title }}</h2></b>
                    <p class="live-meta">
                         <i class="bi bi-geo-alt-fill"></i>

                        <span>{{ $mingle->cityRecord?->name ?: 'City to be announced' }}</span>
                        @if($mingle->type === 'event' && $mingle->starts_at)
                            <span>{{ $mingle->starts_at->format('D, M j') }}</span>
                            <span>{{ $mingle->starts_at->format('g:i A') }}</span>
                        @endif
                    </p>

                    <nav class="live-tabs" aria-label="Mingle details">
                        <a class="is-active" href="#about">About</a>
                        <a href="#details">Details</a>
                        <a href="#attendees">{{ $mingle->type === 'event' ? 'Attendees' : 'Members' }}</a>
                    </nav>

                    <section class="live-about" id="about">
                        <p class="live-section-label">GET TO KNOW THIS {{ $mingle->type === 'event' ? 'EVENT' : 'COMMUNITY' }}</p>
                        <h3>About this {{ $mingle->type }}</h3>
                        <p>{{ $mingle->description ?: 'A new place for people to meet, share experiences, and make meaningful connections.' }}</p>
                        <div class="live-tags">
                            @if($mingle->category)<span>{{ $mingle->category }}</span>@endif
                            @foreach($mingle->tags ?? [] as $tag)<span>{{ $tag }}</span>@endforeach
                        </div>
                    </section>

                    <section class="live-facts" id="details" aria-label="Mingle details">
                        @if($mingle->type === 'event')
                            <div><span class="live-fact-icon">D</span><span><b>{{ $mingle->starts_at?->format('l, F j') ?: 'Date to be announced' }}</b><small>{{ $mingle->starts_at?->format('g:i A') }}@if($mingle->ends_at) &ndash; {{ $mingle->ends_at->format('g:i A') }}@endif</small></span></div>
                            <div><span class="live-fact-icon">L</span><span><b>{{ $mingle->venue ?: 'Venue to be announced' }}</b><small>{{ $mingle->address_details ?: ($mingle->cityRecord?->name ?: 'City to be announced') }}</small></span></div>
                        @else
                            <div><span class="live-fact-icon">C</span><span><b>{{ ucfirst($mingle->visibility) }} community</b><small>{{ $mingle->visibility === 'public' ? 'Anyone can find and join' : 'Membership is moderated' }}</small></span></div>
                            <div><span class="live-fact-icon">E</span><span><b>{{ $mingle->members_can_create_events ? 'Events allowed' : 'Events hosted by admins' }}</b><small>Create experiences inside the community</small></span></div>
                        @endif
                        <div id="attendees"><span class="live-fact-icon">{{ $mingle->type === 'event' ? 'G' : 'M' }}</span><span><b>{{ $mingle->maximum_attendees ? 'Up to '.$mingle->maximum_attendees.' '.($mingle->type === 'event' ? 'guests' : 'members') : 'Everyone is welcome' }}</b><small>{{ $mingle->attendees->count() }} {{ Str::plural($mingle->type === 'event' ? 'attendee' : 'member', $mingle->attendees->count()) }} joined</small></span></div>
                    </section>

                    <section class="mingle-updates" id="updates">
                        <header class="live-attendee-panel__heading"><div><p class="live-section-label">KEEP EVERYONE IN THE LOOP</p><h3>Mingle updates</h3></div></header>
                        @if ((int) $mingle->user_id === (int) auth()->id())
                            <form method="POST" action="{{ route('mingles.updates.store', $mingle) }}" class="mingle-update-form">
                                @csrf
                                <label class="sr-only" for="mingle-update-body">Post an update for members</label>
                                <textarea id="mingle-update-body" name="update_body" rows="2" maxlength="1500" required placeholder="Share an update with Mingle members…">{{ old('update_body') }}</textarea>
                                <x-input-error :messages="$errors->get('update_body')" />
                                <button type="submit">Post update →</button>
                            </form>
                        @endif
                        <div class="mingle-updates__list">
                            @forelse ($mingleUpdates as $update)
                                <article><b>{{ $update->user->name }}</b><small>{{ $update->created_at->format('M j, Y · g:i A') }}</small><p>{{ $update->body }}</p></article>
                            @empty
                                <p class="mingle-chat__empty">There are no updates yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="live-attendee-panel">
                        <div class="live-attendee-panel__heading"><div><p class="live-section-label">YOUR PEOPLE</p><h3>{{ $mingle->type === 'event' ? 'Who’s going' : 'Community members' }}</h3></div><button type="button" onclick="if (navigator.share) { navigator.share({ url: location.href }); } else if (navigator.clipboard) { navigator.clipboard.writeText(location.href).then(() => this.textContent = 'Link copied'); }">Share Mingle</button></div>
                        <div class="live-attendee-list">
                            @forelse ($attendees->take(12) as $attendee)
                                <a href="{{ route('members.show', $attendee) }}" title="{{ $attendee->name }}">
                                    <span class="live-avatar">@if($attendee->profile_photo_path)<img src="{{ asset('storage/'.$attendee->profile_photo_path) }}" alt="">@else{{ strtoupper(substr($attendee->name, 0, 1)) }}@endif</span>
                                    <b>{{ $attendee->name }}</b>
                                </a>
                            @empty
                                <p>No members yet. Be the first to join.</p>
                            @endforelse
                        </div>
                    </section>

                    @if ($isMember)
                        <livewire:messages.mingle-chat :mingle="$mingle" />
                    @endif
                </article>

                <aside class="live-sidebar">
                    <section class="live-join-card">
                        <p>{{ $mingle->type === 'event' ? 'SAVE YOUR SPOT' : 'FIND YOUR PEOPLE' }}</p>
                        <h3>{{ $mingle->type === 'event' ? 'Join this Mingle' : 'Become a member' }}</h3>
                        <span class="live-join-card__count">{{ $mingle->attendees->count() }} {{ $mingle->type === 'event' ? Str::plural('person', $mingle->attendees->count()) : Str::plural('member', $mingle->attendees->count()) }} {{ $mingle->type === 'event' ? 'going' : 'joined' }}</span>
                        <div class="live-actions">
                            @if ((int) $mingle->user_id === (int) auth()->id())
                                <button type="button" class="live-joined" disabled>You are hosting</button>
                            @elseif ($mingle->attendees->contains('id', auth()->id()))
                                <button type="button" class="live-joined" disabled>{{ $mingle->isEvent() ? 'RSVP confirmed' : 'You are a member' }}</button>
                                <form method="POST" action="{{ route('my-mingles.leave', $mingle) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="live-cancel">{{ $mingle->isEvent() ? 'Cancel RSVP' : 'Leave community' }}</button>
                                </form>
                            @elseif ($pendingInvite)
                                <form method="POST" action="{{ route('mingles.join', $mingle) }}">@csrf<button type="submit" class="live-invite">Accept invitation</button></form>
                                <form method="POST" action="{{ route('mingle-invites.decline', $pendingInvite) }}">@csrf<button type="submit" class="live-cancel">Decline invitation</button></form>
                            @elseif ($isRequestPending)
                                <button type="button" class="live-joined" disabled>Request sent · awaiting host</button>
                            @elseif ($mingle->isCommunity() && $mingle->visibility === 'request')
                                <form method="POST" action="{{ route('mingles.join', $mingle) }}">@csrf<button type="submit" class="live-invite">Request to join</button></form>
                            @else
                                <form method="POST" action="{{ route('mingles.join', $mingle) }}">
                                    @csrf
                                    <button type="submit" class="live-invite" @disabled($mingle->isFull())>{{ $mingle->isFull() ? 'Mingle is full' : ($mingle->isEvent() ? 'RSVP now' : 'Join community') }}</button>
                                </form>
                            @endif
                        </div>
                    </section>

                    @if ($pendingJoinRequests->isNotEmpty())
                        <section class="live-host-card live-requests-card">
                            <p>MEMBERSHIP REQUESTS</p>
                            @foreach ($pendingJoinRequests as $pendingMember)
                                <div class="live-request-row">
                                    <a href="{{ route('members.show', $pendingMember) }}">{{ $pendingMember->name }}</a>
                                    <form method="POST" action="{{ route('mingles.join-requests.respond', [$mingle, $pendingMember]) }}">@csrf<input type="hidden" name="action" value="accept"><button type="submit">Accept</button></form>
                                    <form method="POST" action="{{ route('mingles.join-requests.respond', [$mingle, $pendingMember]) }}">@csrf<input type="hidden" name="action" value="decline"><button class="is-decline" type="submit">Decline</button></form>
                                </div>
                            @endforeach
                        </section>
                    @endif

                    @if ($isMember && $inviteCandidates->isNotEmpty())
                        <section class="live-host-card live-mingle-invite-card">
                            <p>BRING A CONNECTION</p>
                            <form method="POST" action="{{ route('mingles.invites.store', $mingle) }}">
                                @csrf
                                <label for="mingle-invite-member">Invite a connection</label>
                                <select id="mingle-invite-member" name="user_id" required><option value="">Choose a member</option>@foreach($inviteCandidates as $candidate)<option value="{{ $candidate->id }}">{{ $candidate->name }}</option>@endforeach</select>
                                <button type="submit">Send invite</button>
                            </form>
                        </section>
                    @endif

                    <section class="live-host-card">
                        <p>YOUR HOST</p>
                        <div class="live-host-card__person">
                            <div class="live-avatar">
                                @if($mingle->host->profile_photo_path)
                                    <img src="{{ asset('storage/'.$mingle->host->profile_photo_path) }}" alt="{{ $mingle->host->name }}">
                                @else
                                    {{ strtoupper(substr($mingle->host->name, 0, 1)) }}
                                @endif
                            </div>
                            <span><b><a href="{{ route('members.show', $mingle->host) }}">{{ $mingle->host->name }}</a></b><small>Community host</small></span>
                        </div>
                        <div class="live-host-card__note">Meet new people and make a connection in your city.</div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
