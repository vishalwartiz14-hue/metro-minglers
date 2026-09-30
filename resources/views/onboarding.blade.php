<x-app-layout>
    <div class = "onboarding-page">
        <header class="onboarding-progress">
            <span>YOUR PROFILE, YOUR PEOPLE</span>
            <div><b>1</b><i></i><b class="is-current">2</b></div>
            <small>Account created <strong>·</strong> Interests &amp; goals</small>
        </header>

        <section class="onboarding-card">
            <p class="onboarding-eyebrow">A BETTER DISCOVER STARTS HERE</p>
            <h1>What brings you to <em>your people?</em></h1>
            <p class="onboarding-intro">Choose the interests and experiences you want to find. You can change these anytime in your profile.</p>

            @if ($errors->any())
                <div class="onboarding-errors">Please review your selections: {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('onboarding.store') }}" class="onboarding-form">
                @csrf
                <fieldset class="onboarding-fieldset">
                    <legend>Your interests <small>Select up to 14</small></legend>
                    <div class="onboarding-interest-grid">
                        @foreach ($interests as $interest)
                            <label>
                                <input type="checkbox" name="interest_ids[]" value="{{ $interest->id }}" @checked(in_array($interest->id, old('interest_ids', [])))>
                                <span><b aria-hidden="true">{{ ['Food & Drinks' => '♨', 'Fitness & Sports' => '✦', 'Outdoors & Adventure' => '△', 'Travel' => '➤', 'Entertainment' => '▣', 'Social & Lifestyle' => '♡', 'Business & Networking' => '⌘', 'Arts & Culture' => '◉', 'Music' => '♫', 'Hobbies' => '✿', 'Health & Wellness' => '✚', 'Dating & Relationships' => '♥', 'Family & Community' => '♧', 'Faith & Spirituality' => '✧'][$interest->name] ?? '✦' }}</b>{{ $interest->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('interest_ids')" />
                </fieldset>

                <fieldset class="onboarding-fieldset">
                    <legend>What would you like to do?</legend>
                    <div class="onboarding-reasons">
                        @foreach ($reasons as $value => $label)
                            <label><input type="checkbox" name="joining_reasons[]" value="{{ $value }}" @checked(in_array($value, old('joining_reasons', [])))><span>{{ $label }}</span></label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('joining_reasons')" />
                </fieldset>

                <label class="dating-toggle">
                    <input type="checkbox" name="dating_mode" value="1" @checked(old('dating_mode'))>
                    <span><b>Dating mode is optional</b><small>Keep dating in its own part of MetroMinglers. Your main community experience stays about people and shared interests.</small></span>
                </label>

                <button class="onboarding-submit" type="submit">Show me my community <b>→</b></button>
            </form>
        </section>
    </div>
</x-app-layout>
