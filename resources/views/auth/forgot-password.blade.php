<x-guest-layout>
    <div class="auth-heading">
        <p>ACCOUNT SUPPORT</p>
        <h1>Reset your password.</h1><span>Enter your email and we’ll send a secure reset link.</span>
    </div>
    <x-auth-session-status class="auth-status" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">@csrf<label for="email">Email
            address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        <x-input-error :messages="$errors->get('email')" /><button class="auth-submit" type="submit">Send Reset Link
            <b>→</b></button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">← Back to login</a></p>
</x-guest-layout>