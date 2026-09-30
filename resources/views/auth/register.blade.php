<x-guest-layout>
    <div class="auth-heading">
        <p>YOUR COMMUNITY AWAITS</p>
        <h1>Join MetroMinglers.</h1>
        <span>Create your account and start connecting in your city.</span>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form auth-form--register">
        @csrf
        <div class="auth-field">
            <label for="name">Your name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div class="auth-field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="auth-field">
            <label for="date_of_birth">Date of birth (18+)</label>
            <input id="date_of_birth" type="text" name="date_of_birth" value="{{ old('date_of_birth') }}" placeholder="Select your date of birth" readonly required>
            <x-input-error :messages="$errors->get('date_of_birth')" />
        </div>
        <div class="auth-field">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <option value="">Select gender</option>
                <option value="woman" @selected(old('gender') === 'woman')>Woman</option>
                <option value="man" @selected(old('gender') === 'man')>Man</option>
                <option value="non_binary" @selected(old('gender') === 'non_binary')>Non-binary</option>
                <option value="prefer_not_to_say" @selected(old('gender') === 'prefer_not_to_say')>Prefer not to say</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" />
        </div>
        <div class="auth-field">
            <label for="city_id">City</label>
            <select id="city_id" name="city_id" required><option value="">Select your city</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id', $selectedCityId) == $city->id)>{{ $city->name }}</option>@endforeach</select>
            <x-input-error :messages="$errors->get('city_id')" />
        </div>
        <div class="auth-field">
            <label for="zip_code">ZIP code</label>
            <input id="zip_code" type="text" name="zip_code" value="{{ old('zip_code') }}" required autocomplete="postal-code">
            <x-input-error :messages="$errors->get('zip_code')" />
        </div>
        <div class="auth-field">
            <label for="password">Create password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="auth-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>
        <button class="auth-submit" type="submit">Create My Account <b>&rarr;</b></button>
    </form>

    <p class="auth-switch">Already a member? <a href="{{ route('login') }}">Log in</a></p>
</x-guest-layout>
