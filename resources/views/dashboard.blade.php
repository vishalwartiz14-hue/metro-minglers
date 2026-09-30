<x-app-layout>
    <style>
    .member-home { padding-top: 26px; }
    .member-hero {
    max-width: 1136px;
    margin: auto;
    min-height: 180px;
    padding: 18px 38px;
    border-radius: 20px;
    overflow: hidden;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(105deg, #0d111c 18%, #1e2637b8), url(https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=1600&q=85) center / cover;
}
    .member-hero h1 { font-size: clamp(28px, 3vw, 36px); line-height: 1.02; letter-spacing: -1px; }
    .member-copy { max-width: 520px; margin: 5px 0 8px; font-size: 12px; line-height: 1.35; }
    .member-hero .mingle-cta { padding: 9px 16px; }
    .member-hero-art { width: 150px; height: 150px; }
    .member-hero-art span { font-size: 34px; }
    .member-hero-art strong { font-size: 16px; }
    @media (max-width: 650px) {
        .member-home { padding-top: 20px; }
        .member-hero { min-height: 0; padding: 20px; }
        .member-hero-art { display: none; }
    }
</style>

    <div class="member-home">
        @if (session('status'))
        <div class="member-toast">✓ {{ session('status') }}</div>
        @endif
        <section class="member-hero">
            <div>
                <p class="member-eyebrow">YOUR CITY · YOUR PEOPLE</p>
                <h1>Make plans. Meet people.<br><em>Find your community.</em></h1>
                <p class="member-copy">Bring people together around the things you love, right where you live.</p>
                <div class="member-hero-actions">
                    <a class="mingle-cta" href="{{ route('mingles.create') }}"><span>＋</span> Create a Mingle</a>
                    <a class="member-discover-link" href="{{ route('discover.index') }}">Discover by Interests <span aria-hidden="true">&rarr;</span></a>
                </div>
                
            </div>
            <div class="member-hero-art" aria-hidden="true"><span>✦</span><strong>Good things<br>happen
                    together.</strong></div>
        </section>

        <section class="member-action-grid">
            <a href="{{ route('mingles.events.create') }}" class="member-action member-action--red"><i>▦</i><b>Create an
                    Event</b><small>Plan a moment people will remember.</small></a>
            <a href="{{ route('mingles.communities.create') }}" class="member-action"><i>◉</i><b>Start a Community</b><small>Build a
                    place for your people.</small></a>
            <a href="{{ route('profile.edit') }}" class="member-action"><i>♡</i><b>Complete Your Profile</b><small>Help
                    the right people find you.</small></a>
        </section>

        <section class="member-steps">
            <div><span>01</span>
                <p><b>Choose your Mingle</b><br>Event or community—both start here.</p>
            </div>
            <div><span>02</span>
                <p><b>Add the details</b><br>Set the vibe, location, and who can join.</p>
            </div>
            <div><span>03</span>
                <p><b>Invite your people</b><br>Publish and let connections happen.</p>
            </div>
        </section>
    </div>
</x-app-layout>
