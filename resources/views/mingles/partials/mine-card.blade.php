<article class="my-mingle-card">
    
    <a class="my-mingle-card__cover" href="{{ route('mingles.show', $mingle) }}" aria-label="View {{ $mingle->title }}">
        @if ($mingle->image_url)
            <img src="{{ $mingle->image_url }}" alt="" loading="lazy" decoding="async">
        @endif
        <span class="my-mingle-card__type">{{ $mingle->isEvent() ? 'EVENT' : 'COMMUNITY' }}</span>
    </a>
    <div class="my-mingle-card__body">
        <p class="my-mingle-card__meta">{{ $mingle->category ?: 'LOCAL MINGLE' }} <span>&middot;</span> {{ $mingle->cityRecord?->name ?: 'Your city' }}</p>
        <h3><a href="{{ route('mingles.show', $mingle) }}">{{ $mingle->title }}</a></h3>
        @if ($mingle->isEvent())
            <div class="my-mingle-card__details">
                <span>{{ $mingle->starts_at?->format('D, M j · g:i A') ?: 'Date to be announced' }}</span>
                @if ($mingle->venue)<span>{{ $mingle->venue }}</span>@endif
                @if ($kind === 'upcoming' || $kind === 'past')<span>Hosted by {{ $mingle->host->name }}</span>@endif
            </div>
        @else
            <div class="my-mingle-card__details">
                <span>{{ $mingle->attendees_count }} {{ Str::plural('member', $mingle->attendees_count) }}</span>
                @if ($kind === 'community')<span>Hosted by {{ $mingle->host->name }}</span>@endif
            </div>
        @endif
        @if ($kind === 'upcoming')
            <div class="my-mingle-card__reminders">
                <span>Email reminder <b>{{ $mingle->pivot->email_reminders ? 'On' : 'Off' }}</b></span>
                <form method="POST" action="{{ route('my-mingles.reminders.update', $mingle) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="email_reminders" value="{{ $mingle->pivot->email_reminders ? 0 : 1 }}">
                    <button type="submit">Turn {{ $mingle->pivot->email_reminders ? 'off' : 'on' }}</button>
                </form>
            </div>
        @endif
        <div class="my-mingle-card__footer">
            <span>
                @if ($kind === 'created')
                    {{ $mingle->attendees_count }} {{ Str::plural($mingle->isEvent() ? 'RSVP' : 'member', $mingle->attendees_count) }}
                @elseif ($kind === 'upcoming')
                    RSVP confirmed
                @elseif ($kind === 'past')
                    Past event
                @else
                    {{ (int) $mingle->user_id === (int) auth()->id() ? 'You’re hosting' : 'Member' }}
                @endif
            </span>
            @if ($kind === 'upcoming' || ($kind === 'community' && (int) $mingle->user_id !== (int) auth()->id()))
                <form method="POST" action="{{ route('my-mingles.leave', $mingle) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="my-mingle-card__secondary">{{ $kind === 'upcoming' ? 'Cancel RSVP' : 'Leave community' }}</button>
                </form>
            @elseif ($kind === 'created')
                <a class="my-mingle-card__action" href="{{ route('mingles.show', $mingle) }}">Manage <b aria-hidden="true">&rarr;</b></a>
            @else
                <a class="my-mingle-card__action" href="{{ route('mingles.show', $mingle) }}">View details <b aria-hidden="true">&rarr;</b></a>
            @endif
        </div>
    </div>
</article>
