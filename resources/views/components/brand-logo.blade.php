@props(['href' => '/', 'variant' => 'light', 'size' => 'regular', 'tagline' => true])

<a class="site-logo site-logo--{{ $variant }} site-logo--{{ $size }}" href="{{ $href }}" aria-label="MetroMinglers home">
    <svg class="site-logo__skyline" width="142" height="18" viewBox="0 0 180 24" fill="none" stroke="#fa123d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="M3 22V12h9V6h9v9h8V3h10v12h8V8h9v14m4 0V12h8V5h10v17m4 0V9h9v13m4 0V4h11v18m4 0V11h8V6h10v16m4 0V9h9v13m4 0V3h10v19m4 0V8h9v14m4 0V12h8v10" />
    </svg>
    <span class="site-logo__word">Metro&#8288;<span>Minglers</span></span>
    @if ($tagline)
        <small class="site-logo__tagline">PEOPLE · INTERESTS · EXPERIENCES · YOUR CITY</small>
    @endif
</a>
