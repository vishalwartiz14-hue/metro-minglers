<nav class="member-nav" x-data="{ open: false }">
    <div class="member-nav__inner">
        <x-brand-logo :href="route('dashboard')" variant="dark" size="compact" :tagline="false" />
        <button class="member-menu" @click="open = !open" aria-label="Toggle menu">☰</button>
        <div class="member-nav__links" :class="{ 'is-open': open }">
            <a @click="open = false" href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Home</a>
            <a @click="open = false" href="{{ route('discover.index') }}" class="{{ request()->routeIs('discover.*') ? 'is-active' : '' }}">Discover</a>
            <a @click="open = false" href="{{ route('my-mingles.index') }}" class="{{ request()->routeIs('my-mingles.*', 'mingles.show') ? 'is-active' : '' }}">My Mingles</a>
            <a @click="open = false" href="{{ route('mingles.create') }}" class="{{ request()->routeIs('mingles.create', 'mingles.store') ? 'is-active' : '' }}">Create</a>
            <a @click="open = false" href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*', 'members.messages*') ? 'is-active' : '' }}">Messages</a>
            <a @click="open = false" href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'is-active' : '' }}">Profile</a>
            @if (Auth::user()->is_admin)
                <a @click="open = false" href="{{ route('admin.directory.index') }}" class="{{ request()->routeIs('admin.directory.*') ? 'is-active' : '' }}">Admin</a>
                <a @click="open = false" href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}">Reports</a>
            @endif
        </div>
        <a class="member-nav__notifications {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}" href="{{ route('notifications.index') }}" aria-label="Notifications">
            ♧@if(Auth::user()->unreadNotifications()->exists())<i></i>@endif
        </a>
        <div class="member-nav__profile" x-data="{ profileOpen: false }" @click.outside="profileOpen = false" @keydown.escape.window="profileOpen = false">
            <button class="member-nav__profile-trigger" type="button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen.toString()" aria-haspopup="menu" aria-label="Open account menu">
                <span class="member-nav__avatar">
                    @if (Auth::user()->profile_photo_path)
                        <img src="{{ asset('storage/'.Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}'s profile photo">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </span>
                <b>{{ Auth::user()->name }}</b>
                <span class="member-nav__profile-chevron" aria-hidden="true">⌄</span>
            </button>
            <div class="member-nav__profile-menu" x-cloak x-show="profileOpen" x-transition.origin.top.right role="menu">
                <div class="member-nav__profile-menu-heading">
                    <b>{{ Auth::user()->name }}</b>
                    <small>{{ Auth::user()->email }}</small>
                </div>
                <a role="menuitem" href="{{ route('profile.edit') }}" @click="profileOpen = false">Profile settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button role="menuitem" type="submit">Log out</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<nav class="member-mobile-nav" aria-label="Primary navigation">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg><span>Home</span>
    </a>
    <a href="{{ route('discover.index') }}" class="{{ request()->routeIs('discover.*') ? 'is-active' : '' }}" @if(request()->routeIs('discover.*')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg><span>Discover</span>
    </a>
    <a href="{{ route('mingles.create') }}" class="member-mobile-nav__create {{ request()->routeIs('mingles.create', 'mingles.store') ? 'is-active' : '' }}" aria-label="Create a Mingle">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>Create</span>
    </a>
    <a href="{{ route('my-mingles.index') }}" class="{{ request()->routeIs('my-mingles.*') ? 'is-active' : '' }}" @if(request()->routeIs('my-mingles.*')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h3M8 17h6"/></svg><span>Mingles</span>
    </a>
    <a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'is-active' : '' }}" @if(request()->routeIs('messages.*')) aria-current="page" @endif>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H7l-4 2 1.5-4.5A7.5 7.5 0 1 1 20 11.5Z"/><path d="M8 11h.01M12 11h.01M16 11h.01"/></svg><span>Messages</span>
    </a>
</nav>
