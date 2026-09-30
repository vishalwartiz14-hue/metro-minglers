<x-guest-layout>
    <div class="auth-heading">
        <p>WELCOME BACK</p>
        <h1>Find Your People.</h1>
        <span>Sign in to keep making meaningful connections.</span>
    </div>
    <x-auth-session-status class="auth-status" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}"
            required autofocus autocomplete="username">
        <x-input-error :messages="$errors->get('email')" /><label for="password">Password</label><input id="password"
            type="password" name="password" required autocomplete="current-password">
        <x-input-error :messages="$errors->get('password')" />
        <div class="auth-options"><label><input id="remember_me" type="checkbox" name="remember"> Remember me</label><a
                href="{{ route('password.request') }}">Forgot password?</a></div><button class="auth-submit"
            type="submit">Log In <b>→</b></button>
    </form>
    <p class="auth-switch">New to MetroMinglers? <a href="{{ route('register') }}">Join now</a></p>
</x-guest-layout>