<x-app-layout>
    <div class="profile-page">
        <header class="profile-hero">
            <p>MEMBER PROFILE</p>
            <h1>Make your profile <em>feel like you.</em></h1>
            <span>The details you share help the right people find and connect with you.</span>
        </header>

        <div class="profile-layout">
            <aside class="profile-sidebar">
                @if ($user->profile_photo_path)
                    <img class="profile-avatar profile-avatar--image" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}'s profile photo">
                @else
                    <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
                <b>{{ $user->name }}</b><small>{{ $user->cityRecord?->name ?: 'MetroMinglers member' }}</small>
                <nav><a class="is-active" href="#profile-details">Profile details</a><a href="#security">Password & security</a><a href="#account">Account settings</a><a href="#safety">Blocked members</a></nav>
            </aside>
            <main class="profile-content">
                <section id="profile-details" class="profile-card">@include('profile.partials.update-profile-information-form')</section>
                <section id="security" class="profile-card profile-card--compact">@include('profile.partials.update-password-form')</section>
                <section id="account" class="profile-card profile-card--compact profile-card--warning">
                    <h2>Deactivate account</h2><p>Hide your profile and prevent sign-in until support reactivates your account.</p>
                    <form method="post" action="{{ route('profile.deactivate') }}" class="account-action">@csrf <input id="deactivate_password" name="password" type="password" placeholder="Confirm your password" required><button type="submit">Deactivate</button></form>
                    <x-input-error :messages="$errors->deactivateAccount->get('password')" class="mt-2" />
                </section>
                <section class="profile-card profile-card--compact profile-card--danger">@include('profile.partials.delete-user-form')</section>
                <section id="safety" class="profile-card profile-card--compact blocked-members-settings">
                    <h2>Blocked members</h2><p>Manage who can see your profile or send you a connection request.</p>
                    <a href="{{ route('members.blocked') }}">View blocked members ({{ $blockedMembers->count() }}) →</a>
                </section>
            </main>
        </div>
    </div>
</x-app-layout>
