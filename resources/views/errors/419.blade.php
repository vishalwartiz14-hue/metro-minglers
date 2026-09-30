<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Page expired | {{ config('app.name', 'MetroMinglers') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="not-found-page">
    <main class="not-found-shell">
        <header class="not-found-header">
            <x-brand-logo variant="light" size="compact" />
            <a class="not-found-help" href="{{ url('/') }}">MetroMinglers home <span aria-hidden="true">&nearr;</span></a>
        </header>

        <section class="not-found-content" aria-labelledby="expired-title">
            <div class="not-found-art" aria-hidden="true">
                <span class="not-found-orbit not-found-orbit--one"></span>
                <span class="not-found-orbit not-found-orbit--two"></span>
                <div class="not-found-profile not-found-profile--one"><span></span><i></i></div>
                <div class="not-found-profile not-found-profile--two"><span></span><i></i></div>
                <div class="not-found-heart">&#9829;</div>
                <span class="not-found-art-number">419</span>
            </div>

            <p class="not-found-eyebrow">SESSION EXPIRED</p>
            <h1 id="expired-title">This page needs a fresh start.</h1>
            <p class="not-found-copy">For your security, this page expired. Go back, refresh it, and try again.</p>
            <div class="not-found-actions">
                <a class="not-found-primary" href="{{ url()->previous(url('/')) }}">Go back and try again <span aria-hidden="true">&rarr;</span></a>
                <a class="not-found-secondary" href="{{ url('/') }}">Back to home</a>
            </div>
            <p class="not-found-code">ERROR CODE <strong>419</strong></p>
        </section>
    </main>
</body>
</html>
