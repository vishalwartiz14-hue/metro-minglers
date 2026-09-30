<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MetroMinglers') }} | Find Your People</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&family=Rock+Salt&display=swap" rel="stylesheet">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <main class="site">
        <section class="hero" id="home">
            <nav class="nav wrap">
                <x-brand-logo href="#home" variant="light" />
                <button class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">☰</button>
                <div class="nav-links">
                    <a class="active" href="#home">Home</a>
                    <a href="#cities">Cities</a>
                    <a href="#events">Events</a>
                    <a href="#groups">Groups</a>
                    <a href="#stories">Stories</a>
                </div>
                <span class="search" aria-hidden="true">⌕</span>
                <a class="login" href="{{ route('login') }}">Log In</a>
                <a class="join" href="{{ route('register') }}">Join Now</a>
            </nav>
            <div class="hero-content wrap">
                <h1>More Than Dating.<br>It’s a <em>Community.</em></h1>
                <p>Meet people who share your interests, join local events, make real connections, and be part of
                    something bigger in your city.</p>
                <div class="hero-buttons">
                    <a class="primary" href="{{ route('register') }}">Join MetroMinglers <b>→</b></a>
                    <button class="watch">▷ <span>Watch Video</span></button>
                </div>
            </div>
            <div class="hero-features wrap">
                <div><i>♙</i>Meet People</div>
                <div><i>◫</i>Join Events</div>
                <div><i>♧</i>Find Your People</div>
                <div><i>♡</i>Dating (Optional)</div>
            </div>
        </section>

        <section class="cities" id="cities">
            <div class="wrap">
                <header class="heading light">
                    <h2>CHOOSE YOUR CITY</h2>
                    <p>One community. Your city. Your connections.</p>
                </header>
                <div class="city-grid">
                    @foreach ($cities as $city)
                        <a class="city city--dynamic" href="{{ route('cities.show', $city) }}"
                            style="background-image:linear-gradient(180deg,transparent 25%,rgba(0,0,0,.88)),url('{{ $city->landing_image_url }}')">
                            <h3>{{ $city->name }}</h3>
                            <p>{{ $city->tagline ?: 'Your city. Your connections.' }}</p><b>›</b>
                        </a>
                    @endforeach
                </div>

                @if (false)
                    <div class="city-grid">
                        <a class="city atlanta" href="{{ route('cities.show', ['city' => 'ATL']) }}">
                            <h3>Atlanta</h3><p>Big Energy. Real People.</p><b>›</b>
                        </a>
                        <a class="city charlotte" href="{{ route('cities.show', ['city' => 'CLT']) }}">
                            <h3>Charlotte</h3><p>Queen City Vibes.</p><b>›</b>
                        </a>
                        <a class="city dallas" href="{{ route('cities.show', ['city' => 'DAL']) }}">
                            <h3>Dallas</h3><p>Bigger Connections.</p><b>›</b>
                        </a>
                        <a class="city houston" href="{{ route('cities.show', ['city' => 'HOU']) }}">
                            <h3>Houston</h3><p>Diverse. Driven. Dynamic.</p><b>›</b>
                        </a>
                        <a class="city miami" href="{{ route('cities.show', ['city' => 'MIA']) }}">
                            <h3>Miami</h3><p>Sun, Fun, and Great People.</p><b>›</b>
                        </a>
                    </div>
                @endif
            </div>
        </section>

        <section class="stories" id="stories">
            <div class="wrap">
                <header class="heading"><h2>REAL PEOPLE. REAL EXPERIENCES.</h2></header>
                <div class="testimonial-grid">
                    <article>
                        <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=160&q=80" alt="Tasha">
                        <div><q>I joined for the events, met an amazing group of people, and now I have a whole new circle!</q>
                            <cite>— Tasha, Atlanta</cite><span>★★★★★</span></div>
                    </article>
                    <article>
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=160&q=80" alt="Marcus">
                        <div><q>Found gym buddies, travel partners, and even started a business connection here.</q>
                            <cite>— Marcus, Dallas</cite><span>★★★★★</span></div>
                    </article>
                    <article>
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=160&q=80" alt="Sophia">
                        <div><q>MetroMinglers is different. It’s real people, real interests, real opportunities.</q>
                            <cite>— Sophia, Miami</cite><span>★★★★★</span></div>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta" id="events">
            <div class="wrap cta-inner">
                <h2>Same Cities. More Connections.</h2>
                <div>
                    <a class="primary" href="{{ route('register') }}">Join Today <b>→</b></a>
                    <p>FRIENDSHIPS. EXPERIENCES. OPPORTUNITIES. AND YES... DATING TOO.</p>
                </div>
            </div>
        </section>
    </main>
    @livewireScriptConfig
</body>

</html>
