<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $city->name }} Mingles | {{ config('app.name', 'MetroMinglers') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="city-page">
    <main class="city-directory">
        <header class="city-directory__hero" style="background-image:linear-gradient(105deg,rgba(9,13,24,.82),rgba(9,13,24,.24)),url('{{ $city->landing_image_url }}')">
            <div class="city-directory__inner">
                <div class="city-directory__topline">
                    <x-brand-logo variant="light" size="compact" />
                    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="city-directory__back">&larr; {{ auth()->check() ? 'Back to dashboard' : 'Back to home' }}</a>
                </div>
                <p>EXPLORE YOUR CITY</p>
                <h1>Meet your people in {{ $city->name }}.</h1>
                <span class="city-directory__tagline">{{ $city->tagline ?: 'Public events and communities happening nearby.' }}</span>
                <div class="city-directory__hero-actions">
                    @auth
                        <a href="{{ route('discover.index', ['city_id' => $city->id]) }}" class="city-directory__join">Explore Mingles <b aria-hidden="true">&rarr;</b></a>
                    @else
                        <a href="{{ route('register', ['city_id' => $city->id]) }}" class="city-directory__join">Join your city <b aria-hidden="true">&rarr;</b></a>
                    @endauth
                </div>
            </div>
        </header>

        <section class="city-directory__content">
            <div class="city-directory__heading">
                <div>
                    <p>{{ $mingles->count() }} {{ Str::plural('Mingle', $mingles->count()) }} to explore</p>
                    <h2>What's happening in {{ $city->name }}</h2>
                </div>
                @guest
                    <a href="{{ route('register', ['city_id' => $city->id]) }}" class="city-directory__join">Join MetroMinglers</a>
                @else
                    <a href="{{ route('discover.index', ['city_id' => $city->id]) }}" class="city-directory__join">Explore all Mingles</a>
                @endguest
            </div>

            @if ($mingles->isEmpty())
                <div class="city-directory__empty">
                    <h2>New connections are coming soon.</h2>
                    <p>There are no public Mingles in {{ $city->name }} yet.</p>
                    <a href="{{ route('register', ['city_id' => $city->id]) }}">Join to be the first to create one</a>
                </div>
            @else
                <div class="city-directory__grid">
                    @foreach ($mingles as $mingle)
                        <article class="city-mingle-card">
                            <div class="city-mingle-card__image" @if ($mingle->image_url) style="--mingle-cover: url('{{ $mingle->image_url }}')" @endif>
                                @if ($mingle->image_url)
                                    <img src="{{ $mingle->image_url }}" alt="{{ $mingle->title }} cover">
                                @endif
                                <span>{{ $mingle->isEvent() ? 'EVENT' : 'COMMUNITY' }}</span>
                            </div>
                            <div class="city-mingle-card__body">
                                <p>{{ $mingle->category }}</p>
                                <h3><a href="{{ auth()->check() ? route('mingles.show', $mingle) : route('register', ['city_id' => $city->id]) }}">{{ $mingle->title }}</a></h3>
                                @if ($mingle->isEvent())
                                    <small>{{ $mingle->starts_at?->format('D, M j - g:i A') }} &middot; {{ $mingle->venue }}</small>
                                @else
                                    <small>{{ $mingle->attendees_count }} {{ Str::plural('member', $mingle->attendees_count) }} &middot; Hosted by {{ $mingle->host->name }}</small>
                                @endif
                                <div>
                                    <span>{{ $mingle->attendees_count }} {{ Str::plural('going', $mingle->attendees_count) }}</span>
                                    @auth
                                        <a href="{{ route('mingles.show', $mingle) }}">View Mingle</a>
                                    @else
                                        <a href="{{ route('register', ['city_id' => $city->id]) }}">Join to connect</a>
                                    @endauth
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
    
</body>
</html>
