<x-app-layout>
    <div class="my-mingles-page">
        <header class="my-mingles-hero">
            <div>
                <p>YOUR MINGLES</p>
                <h1>Your plans,<br><em>all in one place.</em></h1>
                <span>Keep track of the Mingles you host, the events you’re going to, and the communities you belong to.</span>
            </div>
            <a class="my-mingles-create" href="{{ route('mingles.create') }}">Create a Mingle <b aria-hidden="true">&rarr;</b></a>
        </header>

        @if (session('status'))
            <div class="member-toast">{{ session('status') }}</div>
        @endif

        <nav class="my-mingles-tabs" aria-label="Your Mingles">
            <a href="#created">Created</a>
            <a href="#upcoming">Upcoming RSVPs</a>
            <a href="#past">Past RSVPs</a>
            <a href="#communities">Communities</a>
        </nav>

        <section class="my-mingles-section" id="created">
            <div class="my-mingles-heading">
                <div><p>HOSTING</p><h2>Mingles you created</h2></div>
                <span>{{ $created->count() }} shown</span>
            </div>
            @forelse ($created as $mingle)
                @if ($loop->first)<div class="my-mingles-grid">@endif
                @include('mingles.partials.mine-card', ['mingle' => $mingle, 'kind' => 'created'])
                @if ($loop->last)</div>@endif
            @empty
                <div class="my-mingles-empty"><h3>Your next Mingle starts here.</h3><p>Create an event or community and it will appear here.</p><a href="{{ route('mingles.create') }}">Create a Mingle</a></div>
            @endforelse
            @if ($created->hasPages())<div class="my-mingles-pagination">{{ $created->links() }}</div>@endif
        </section>

        <section class="my-mingles-section" id="upcoming">
            <div class="my-mingles-heading">
                <div><p>ON YOUR CALENDAR</p><h2>Upcoming RSVPs</h2></div>
                <span>{{ $upcomingRsvps->count() }} shown</span>
            </div>
            @forelse ($upcomingRsvps as $mingle)
                @if ($loop->first)<div class="my-mingles-grid">@endif
                @include('mingles.partials.mine-card', ['mingle' => $mingle, 'kind' => 'upcoming'])
                @if ($loop->last)</div>@endif
            @empty
                <div class="my-mingles-empty"><h3>No upcoming RSVPs yet.</h3><p>Find an event in your city and save your spot.</p><a href="{{ route('discover.index') }}">Explore Mingles</a></div>
            @endforelse
            @if ($upcomingRsvps->hasPages())<div class="my-mingles-pagination">{{ $upcomingRsvps->links() }}</div>@endif
        </section>

        <section class="my-mingles-section" id="past">
            <div class="my-mingles-heading">
                <div><p>MEMORIES</p><h2>Past RSVPs</h2></div>
                <span>{{ $pastRsvps->count() }} shown</span>
            </div>
            @forelse ($pastRsvps as $mingle)
                @if ($loop->first)<div class="my-mingles-grid">@endif
                @include('mingles.partials.mine-card', ['mingle' => $mingle, 'kind' => 'past'])
                @if ($loop->last)</div>@endif
            @empty
                <div class="my-mingles-empty"><h3>Your event history will show here.</h3><p>Events you’ve attended stay easy to find.</p></div>
            @endforelse
            @if ($pastRsvps->hasPages())<div class="my-mingles-pagination">{{ $pastRsvps->links() }}</div>@endif
        </section>

        <section class="my-mingles-section" id="communities">
            <div class="my-mingles-heading">
                <div><p>YOUR PEOPLE</p><h2>Communities you’re a member of</h2></div>
                <span>{{ $communities->count() }} shown</span>
            </div>
            @forelse ($communities as $mingle)
                @if ($loop->first)<div class="my-mingles-grid">@endif
                @include('mingles.partials.mine-card', ['mingle' => $mingle, 'kind' => 'community'])
                @if ($loop->last)</div>@endif
            @empty
                <div class="my-mingles-empty"><h3>Find your community.</h3><p>Join a group built around something you love.</p><a href="{{ route('discover.index', ['type' => 'community']) }}">Explore communities</a></div>
            @endforelse
            @if ($communities->hasPages())<div class="my-mingles-pagination">{{ $communities->links() }}</div>@endif
        </section>
    </div>
</x-app-layout>

