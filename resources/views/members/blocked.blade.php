<x-app-layout>
    <div class="blocked-members-page">
        <a class="member-profile-back" href="{{ route('profile.edit') }}">← Back to Profile</a>
        <header><p>SAFETY &amp; PRIVACY</p><h1>Blocked members</h1><span>Blocked members cannot see your profile or send you connection requests and messages.</span></header>

        @if (session('status'))<div class="member-toast">{{ session('status') }}</div>@endif

        @forelse ($members as $member)
            <article class="blocked-member-row">
                <div class="blocked-member-avatar">@if($member->profile_photo_path)<img src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="">@else{{ strtoupper(substr($member->name, 0, 1)) }}@endif</div>
                <div><b>{{ $member->name }}</b><small>{{ $member->cityRecord?->name ?: 'MetroMinglers member' }}</small></div>
                <form method="POST" action="{{ route('members.unblock', $member) }}">@csrf @method('DELETE')<button type="submit">Unblock</button></form>
            </article>
        @empty
            <div class="blocked-members-empty">You haven’t blocked any members.</div>
        @endforelse
    </div>
</x-app-layout>
