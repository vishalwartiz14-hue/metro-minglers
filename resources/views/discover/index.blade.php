<x-app-layout>
    <div class="discover-page" x-data="{ interestsOpen: false, selectedInterestCount: {{ count($selectedInterestIds) }} }">
        <header class="discover-mobile-heading">
            <div><p>DISCOVER YOUR CITY</p><h1>Find your people</h1></div>
            <a href="{{ route('messages.index') }}" aria-label="Messages">&#9993;</a>
        </header>
        <header class="discover-hero">
            <div class="discover-hero__copy">
                <p>DISCOVER YOUR CITY</p>
                <h1>Local Mingles.<br><em>Real connections.</em></h1>
                <span>Find your people, make a plan, and enjoy more of your city together.</span>
            </div>
            <div class="discover-hero__actions">
                
                <a class="discover-hero__create" href="{{ route('mingles.create') }}">
                    <span>HAVE AN IDEA?</span>
                    <strong>Create a Mingle <b aria-hidden="true">→</b></strong>
                    <small>Bring your people together.</small>
                </a>
            </div>
        </header>

        @if (session('status'))
            <div class="member-toast">{{ session('status') }}</div>
        @endif

        @if ($pendingConnections->isNotEmpty())
            <section class="connection-requests">
                <header><div><p>NEW CONNECTIONS</p><h2>Requests for you</h2></div><span>{{ $pendingConnections->count() }} waiting</span></header>
                @foreach ($pendingConnections as $connection)
                    <article class="connection-request">
                        <a class="connection-request__person" href="{{ route('members.show', $connection->sender) }}">
                            <span class="discover-avatar">@if($connection->sender->profile_photo_path)<img src="{{ asset('storage/'.$connection->sender->profile_photo_path) }}" alt="">@else{{ strtoupper(substr($connection->sender->name, 0, 1)) }}@endif</span>
                            <b>{{ $connection->sender->name }}</b>
                        </a>
                        <form method="POST" action="{{ route('connections.respond', $connection) }}">@csrf<input type="hidden" name="action" value="accept"><button type="submit">Accept</button></form>
                        <form method="POST" action="{{ route('connections.respond', $connection) }}">@csrf<input type="hidden" name="action" value="decline"><button class="connection-request__decline" type="submit">Decline</button></form>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="discover-interest-summary" aria-label="Selected interests">
            <div class="discover-interest-chips">
                @php($visibleInterests = $interests->whereIn('id', $selectedInterestIds))
                @forelse ($visibleInterests as $interest)
                    <span>{{ $interest->name }}</span>
                @empty
                    <span class="discover-interest-empty">Choose interests to personalize your results</span>
                @endforelse
            </div>
            <button type="button" class="discover-change-interests" @click="interestsOpen = true">Change Interests</button>
        </section>

        <form class="discover-filter-panel" method="GET" action="{{ route('discover.index') }}" x-data="{ filtersOpen: false }">
            <input type="hidden" name="filters_applied" value="1">
            <input type="hidden" name="section" value="{{ $section }}">
            <div class="discover-filter-bar">
                <button class="discover-filter-toggle" type="button" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()">
                    <span class="discover-filter-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h9m4 0h3M4 17h3m4 0h9M13 4v6M9 14v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <span class="discover-filter-label"><b>Filter Results</b><small>Location, age, interests and more</small></span>
                    @if ($activeFilterCount)
                        <span class="discover-filter-count">{{ $activeFilterCount }} applied</span>
                    @endif
                    <svg class="discover-filter-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                @if ($activeFilterCount)
                    <a class="discover-filter-clear" href="{{ route('discover.index') }}">Clear</a>
                @endif
            </div>

            <div class="discover-filter-content" x-cloak x-show="filtersOpen" x-transition>
                <div class="discover-filters-grid">
                    <div>
                        <label for="discover-city">Near city</label>
                        <select id="discover-city" name="city_id">
                            <option value="" @selected($selectedCityId === null)>All cities</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected($selectedCityId == $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="discover-distance">Within</label>
                        <select id="discover-distance" name="distance">
                            <option value="">Any distance</option>
                            @foreach ([25, 50, 100, 250] as $miles)
                                <option value="{{ $miles }}" @selected($radius == $miles)>{{ $miles }} miles</option>
                            @endforeach
                        </select>
                        <small class="discover-field-note">Approximate distance from city center</small>
                    </div>
                    <div class="discover-age-filter">
                        <label for="discover-min-age">Age range</label>
                        <div><input id="discover-min-age" type="number" name="min_age" min="18" max="100" value="{{ $minAge }}" placeholder="18"><span>to</span><input aria-label="Maximum age" type="number" name="max_age" min="18" max="100" value="{{ $maxAge }}" placeholder="100"></div>
                    </div>
                    <div>
                        <label for="discover-type">Mingle type</label>
                        <select id="discover-type" name="type">
                            <option value="">All types</option>
                            <option value="event" @selected(request('type') === 'event')>Events</option>
                            <option value="community" @selected(request('type') === 'community')>Communities</option>
                        </select>
                    </div>
                    <div>
                        <label for="discover-date">Event date</label>
                        <input id="discover-date" type="date" name="date" value="{{ request('date') }}">
                    </div>
                    <label class="discover-online-filter"><input type="checkbox" name="online" value="1" @checked($onlineOnly)> <span>Online now</span></label>
                    <div class="discover-filter-actions">
                        <button type="submit">Update results</button>
                        @if ($activeFilterCount)
                            <a href="{{ route('discover.index') }}">Reset</a>
                        @endif
                    </div>
                    <fieldset>
                        <legend>Interests</legend>
                        <div class="discover-interest-list">
                            @foreach ($interests as $interest)
                                <label><input type="checkbox" name="interest_ids[]" value="{{ $interest->id }}" @checked(in_array($interest->id, $selectedInterestIds))><span>{{ $interest->name }}</span></label>
                            @endforeach
                        </div>
                    </fieldset>
                </div>
            </div>
        </form>

        <nav class="discover-tabs" aria-label="Discover results">
            <a class="{{ $section === 'all' ? 'is-active' : '' }}" href="{{ route('discover.index', array_merge(request()->query(), ['section' => 'all'])) }}">Everything</a>
            <a class="{{ $section === 'people' ? 'is-active' : '' }}" href="{{ route('discover.index', array_merge(request()->query(), ['section' => 'people'])) }}">People</a>
            <a class="{{ $section === 'mingles' ? 'is-active' : '' }}" href="{{ route('discover.index', array_merge(request()->query(), ['section' => 'mingles'])) }}">Mingles</a>
            <span class="discover-view-switch"><a class="{{ $layout === 'grid' ? 'is-active' : '' }}" href="{{ route('discover.index', array_merge(request()->query(), ['layout' => 'grid'])) }}" aria-label="Card view">▦</a><a class="{{ $layout === 'list' ? 'is-active' : '' }}" href="{{ route('discover.index', array_merge(request()->query(), ['layout' => 'list'])) }}" aria-label="List view">☷</a></span>
        </nav>

        @if ($section !== 'mingles')
            <section class="discover-section" id="people">
                <div class="discover-section__title">
                    <div><p>YOUR COMMUNITY</p><h2>People Near You</h2></div>
                    <span>{{ $people->count() }} profiles</span>
                </div>
                @forelse ($people as $person)
                    @if ($loop->first)
                    <div class="discover-people {{ $layout === 'list' ? 'discover-people--list' : '' }}">
                        @endif
                    <article class="discover-person">
                        <a class="discover-person__main" href="{{ route('members.show', $person) }}">
                            <div class="discover-avatar">
                                @if ($person->profile_photo_path)
                                <img src="{{ asset('storage/'.$person->profile_photo_path) }}" 
                                alt="{{ $person->name }}" loading="lazy" decoding="async">
                                @else{{ strtoupper(substr($person->name, 0, 1)) }}@endif
                            </div>
                            <div class="discover-person__info">
                                <h3>{{ $person->name }}@if($person->date_of_birth), {{ $person->date_of_birth->age }}@endif</h3>
                                <p>{{ $person->cityRecord?->name ?: 'MetroMinglers member' }}@if($person->occupation) · {{ $person->occupation }}@endif</p>
                            </div>
                        </a>
                        <span class="discover-person__online {{ $person->isOnline() ? 'is-online' : '' }}"><i></i>{{ $person->isOnline() ? 'Online now' : 'Member' }}</span>
                        <div class="discover-person__interests">
                            @forelse ($person->interestOptions->take(4) as $interest)<span>{{ $interest->name }}</span>@empty<span>New member</span>@endforelse
                        </div>
                        <a class="discover-person__view" href="{{ route('members.show', $person) }}">View profile <b>→</b></a>
                    </article>
                    @if ($loop->last)</div>@endif
                @empty
                    <div class="discover-empty">No people match these filters yet. Try a wider distance, another city, or a different interest.</div>
                @endforelse
            </section>
        @endif

        @if ($section !== 'people')
            <section class="discover-section" id="mingles">
                <div class="discover-section__title">
                    <div><p>AROUND YOU</p><h2>Upcoming Mingles</h2></div>
                    <span>{{ $mingles->count() }} shown</span>
                </div>
                @forelse ($mingles as $mingle)
                    @if ($loop->first)<div class="discover-mingles {{ $layout === 'list' ? 'discover-mingles--list' : '' }}">@endif
                    <article class="discover-mingle">
                        <a class="discover-mingle__image" href="{{ route('mingles.show', $mingle) }}" aria-label="View {{ $mingle->title }}">
                            @if ($mingle->image_url)<img src="{{ $mingle->image_url }}" alt="" loading="lazy" decoding="async">@endif
                            <span class="discover-mingle__type">{{ $mingle->isEvent() ? 'EVENT' : 'COMMUNITY' }}</span>
                            <span class="discover-mingle__visibility">{{ $mingle->visibility === 'open' ? 'OPEN TO JOIN' : 'PUBLIC' }}</span>
                        </a>
                        <div class="discover-mingle__body">
                            <p class="discover-mingle__meta">{{ $mingle->category ?: 'LOCAL MINGLE' }} <span>·</span> {{ $mingle->cityRecord?->name ?: 'Your city' }}</p>
                            <h3><a href="{{ route('mingles.show', $mingle) }}">{{ $mingle->title }}</a></h3>
                            <div class="discover-mingle__details">
                                @if ($mingle->isEvent())
                                    <span><b>WHEN</b>{{ $mingle->starts_at?->format('D, M j · g:i A') ?: 'Date to be announced' }}</span>
                                    @if ($mingle->venue)<span><b>WHERE</b>{{ $mingle->venue }}</span>@endif
                                @else
                                    <span><b>HOST</b><a href="{{ route('members.show', $mingle->host) }}">{{ $mingle->host->name }}</a></span>
                                    <span><b>MEMBERS</b>{{ $mingle->attendees_count }} {{ Str::plural('member', $mingle->attendees_count) }}</span>
                                @endif
                            </div>
                            <div class="discover-mingle__footer">
                                <span class="discover-mingle__attendees">{{ $mingle->attendees_count }}{{ $mingle->maximum_attendees ? '/'.$mingle->maximum_attendees : '' }} {{ $mingle->isEvent() ? 'going' : 'members' }}</span>
                                @if ($mingle->joined_by_current_user)<span class="discover-mingle__joined">Joined</span>
                                @elseif ($mingle->requested_by_current_user)<span class="discover-mingle__joined">Request sent</span>
                                @else<form method="POST" action="{{ route('mingles.join', $mingle) }}">@csrf<button type="submit" @disabled($mingle->isFull())>{{ $mingle->isFull() ? 'Full' : ($mingle->visibility === 'request' ? 'Request to join' : 'Join Mingle') }}</button></form>@endif
                            </div>
                        </div>
                    </article>
                    @if ($loop->last)</div>@endif
                @empty
                    <div class="discover-empty">No Mingles match this search. Be the first to create one.</div>
                @endforelse
                @if ($mingles->hasPages())<nav class="discover-pagination" aria-label="Mingle results pages">{{ $mingles->onEachSide(1)->links() }}</nav>@endif
            </section>
        @endif

        <div class="discover-interest-modal" x-cloak x-show="interestsOpen" x-transition.opacity @click.self="interestsOpen = false" @keydown.escape.window="interestsOpen = false">
            <section class="discover-interest-dialog" role="dialog" aria-modal="true" aria-labelledby="discover-interest-title">
                <header class="discover-interest-dialog__header">
                    <button type="button" @click="interestsOpen = false" aria-label="Close interest selector">&larr;</button>
                    <h2 id="discover-interest-title">Select Your Interests</h2>
                    <button type="button" class="discover-interest-skip" @click="interestsOpen = false">Skip</button>
                </header>
                <p class="discover-interest-dialog__intro">Choose one or more interests to discover people and Mingles that match your vibe.</p>
                <form x-ref="interestForm" method="GET" action="{{ route('discover.index') }}">
                    <input type="hidden" name="filters_applied" value="1">
                    <input type="hidden" name="section" value="{{ $section }}">
                    <input type="hidden" name="layout" value="{{ $layout }}">
                    <input type="hidden" name="city_id" value="{{ $selectedCityId }}">
                    <input type="hidden" name="distance" value="{{ $radius }}">
                    <input type="hidden" name="min_age" value="{{ $minAge }}">
                    <input type="hidden" name="max_age" value="{{ $maxAge }}">
                    <input type="hidden" name="type" value="{{ request('type') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    @if ($onlineOnly)<input type="hidden" name="online" value="1">@endif
                    <div class="discover-interest-tiles">
                        @foreach ($interests as $interest)
                            <label>
                                <input type="checkbox" name="interest_ids[]" value="{{ $interest->id }}" @checked(in_array((int) $interest->id, $selectedInterestIds, true)) @change="selectedInterestCount = $refs.interestForm.querySelectorAll('input[name=&quot;interest_ids[]&quot;]:checked').length">
                                <span>{{ $interest->name }}<b aria-hidden="true">&#10003;</b></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="discover-interest-dialog__actions">
                        <button type="submit">Show Results <span x-text="'(' + selectedInterestCount + ' selected)'">({{ count($selectedInterestIds) }} selected)</span></button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
