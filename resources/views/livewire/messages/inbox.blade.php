<section class="messages-page">
    <header class="messages-heading">
        <div>
            <p>YOUR CONNECTIONS</p>
            <h1>Messages</h1>
            <span>Live conversations with your connections. Message content is not saved.</span>
        </div>
    </header>

    <label class="message-inbox-search">
        <span class="sr-only">Search connections</span>
        <input type="search" wire:model.live.debounce.250ms="search" placeholder="Search connections&hellip;">
    </label>

    @forelse ($partners as $partner)
        <a class="message-thread-row" href="{{ route('members.messages', $partner) }}">
            <div class="message-thread-avatar">
                @if ($partner->profile_photo_path)
                    <img src="{{ asset('storage/'.$partner->profile_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr($partner->name, 0, 1)) }}
                @endif
            </div>
            <div class="message-thread-copy">
                <div class="message-thread-top"><b>{{ $partner->name }}</b><small>Open live conversation</small></div>
                <p>Messages are only available while the conversation is open.</p>
            </div>
        </a>
    @empty
        <div class="blocked-members-empty">
            {{ $search ? 'No connections match your search.' : 'Your inbox is ready when you connect with someone.' }}
            @if (! $search)
                <a href="{{ route('discover.index') }}">Discover members</a>
            @endif
        </div>
    @endforelse
</section>
