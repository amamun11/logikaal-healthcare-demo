@extends('portal.layout')
@section('title', 'Health check packages')
@section('content')
<section class="hero"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow">Plan a health check</span><h1>Health check packages</h1><p class="muted">Explore example package formats and ask the hospital which tests and preparation steps are suitable for you.</p><div class="hero-actions"><a class="btn" href="{{ url('/contact') }}">Ask about a package →</a><a class="btn btn-outline" href="{{ url('/tests') }}">Browse individual tests</a></div></div><div class="hero-art" aria-hidden="true"><div class="hero-orbit"><div class="hero-cross"></div></div></div></div></section>
<div class="container content">
    <div class="disclosure" role="note"><strong>Illustrative examples:</strong> The package names, inclusions, and prices below are for layout review only. They are not approved Logikaal healthcare services or rates. Package contents and fees must be reviewed by the hospital and a qualified clinician before patients rely on them. A clinician should decide which tests are appropriate for each person.</div>
    <section class="section"><div class="section-head"><div><span class="eyebrow">Example formats</span><h2>Choose a conversation to start</h2><p class="muted">Final test selection depends on clinical review and hospital availability.</p></div></div>
        <div class="grid">
        @foreach($packages as $package)
            <article class="card" style="display:flex;flex-direction:column;align-items:flex-start">
                <span class="badge badge-warn">Illustrative information</span>
                <h2 style="font-size:24px;margin-top:17px">{{ $package['name'] }}</h2>
                <p class="muted">{{ $package['summary'] }}</p>
                <h3 style="font-size:15px;margin-top:4px">Example format may include</h3>
                <ul class="info-list" style="width:100%;margin:4px 0 16px">
                    @foreach($package['includes'] as $item)<li>{{ $item }}</li>@endforeach
                </ul>
                <p style="font-size:22px;font-weight:800;color:var(--ink);margin-top:auto">Illustrative price: ৳{{ number_format($package['price']) }}</p>
                <a class="btn btn-soft btn-small" href="{{ url('/contact') }}">Ask the hospital to confirm →</a>
            </article>
        @endforeach
        </div>
    </section>
    <section class="section"><div class="contact-grid"><article class="card"><span class="eyebrow">Before a test</span><h2 style="font-size:24px">Check your preparation instructions</h2><p class="muted">Some tests require preparation and others do not. Follow the instructions given by the hospital or your clinician. Do not change medicines or fast unless a qualified professional tells you to.</p><a href="{{ url('/tests') }}">View individual test information →</a></article><article class="card"><span class="eyebrow">Need help?</span><h2 style="font-size:24px">Confirm the current price</h2><p class="muted">The package examples above are not the hospital’s rate card. Confirm current availability, included tests, preparation, and total fees before booking.</p><a class="btn" href="{{ url('/contact') }}">Contact &amp; directions →</a></article></div></section>
</div>
@endsection
