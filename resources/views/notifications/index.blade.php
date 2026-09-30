<x-app-layout>
    <div class="messages-page notifications-page">
        <header class="messages-heading"><div><p>YOUR CITY, YOUR COMMUNITY</p><h1>Notifications</h1><span>Mingle hosts can share updates with their members here.</span></div></header>
        @forelse ($notifications as $notification)
            <form class="notification-row {{ $notification->read_at ? '' : 'is-unread' }}" method="POST" action="{{ route('notifications.open', $notification->id) }}">
                @csrf
                <span class="notification-row__icon">{{ $notification->read_at ? '✓' : '•' }}</span>
                <button type="submit"><b>{{ $notification->data['title'] ?? 'MetroMinglers update' }}</b><span>{{ $notification->data['body'] ?? '' }}</span><small>{{ $notification->created_at->diffForHumans() }}</small></button>
                <span class="notification-row__arrow">→</span>
            </form>
        @empty
            <div class="blocked-members-empty">You’re all caught up. Mingle updates will appear here.</div>
        @endforelse
        <div class="moderation-pagination">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
