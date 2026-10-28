@extends('portal.layout')
@section('title', 'Healthcare in Rajshahi')
@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Logikaal healthcare <span aria-hidden="true">·</span> Rajshahi</span>
            <h1>Find care with<br>more confidence.</h1>
            <p class="muted">Explore departments, learn about doctors, check chamber updates, and request an appointment—all in one place.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ url('/find-doctor') }}">Find a doctor <span aria-hidden="true">→</span></a>
                <a class="btn btn-outline" href="{{ url('/appointment-info') }}">How appointments work</a>
            </div>
            <div class="hero-location"><span aria-hidden="true">⌖</span> Laxmipur, Rajpara, Rajshahi 6000</div>
        </div>
        <div class="hero-art" aria-label="Healthcare services and appointment planning">
            <div class="hero-orbit"><div class="hero-cross"><svg class="hero-pulse" viewBox="0 0 245 75" aria-hidden="true"><path d="M8 39h54l16-21 25 45 22-50 25 38 15-15h62"/></svg></div></div>
            <div class="hero-float hero-float-top"><span class="float-icon" aria-hidden="true">✓</span><span><strong>Plan your visit</strong><small>Appointments &amp; chamber updates</small></span></div>
            <div class="hero-float hero-float-bottom"><span class="float-icon" aria-hidden="true">⌕</span><span><strong>Explore services</strong><small>Doctors, specialties &amp; tests</small></span></div>
        </div>
    </div>
</section>

<div class="feature-band"><div class="container grid">
    <div class="feature"><span aria-hidden="true">⌕</span><div><b>Find care by specialty</b><small>Explore departments and services</small></div></div>
    <div class="feature"><span aria-hidden="true">▣</span><div><b>Plan an appointment</b><small>Choose a doctor and a time</small></div></div>
    <div class="feature"><span aria-hidden="true">⌖</span><div><b>Visit us in Rajshahi</b><small>Laxmipur, Rajpara · 6000</small></div></div>
</div></div>

<div class="container">
    <section class="section">
        <div class="section-head"><div><span class="eyebrow">Explore care</span><h2>Find the right specialty</h2><p class="muted">Start with a department and explore the available care team.</p></div><a class="btn btn-outline btn-small" href="{{ url('/specialties') }}">All specialties →</a></div>
        <div class="grid">
            @forelse($departments as $department)
                <a class="card specialty-card" href="{{ url('/specialties/'.rawurlencode($department)) }}"><div><span class="specialty-icon" aria-hidden="true">✚</span><h3>{{ $department }}</h3></div><span class="muted">Meet the team →</span></a>
            @empty
                <div class="card empty">Department information will appear here when it is added.</div>
            @endforelse
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div><span class="eyebrow">Our directory</span><h2>Meet our doctors</h2><p class="muted">Review specialties, qualifications, chamber schedules, and appointment availability.</p></div><a class="btn btn-outline btn-small" href="{{ url('/find-doctor') }}">Search doctors →</a></div>
        <div class="grid">
            @forelse($doctors as $doctor)
                <article class="card doctor-card"><div class="doctor-top"></div><div class="inner"><span class="photo" style="display:grid;place-items:center;font-size:24px;font-weight:800;color:var(--teal)">{{ mb_substr($doctor->name, 0, 1) }}</span><h3 style="margin-top:15px">{{ $doctor->name }}</h3><p class="muted">{{ $doctor->department ?: 'Specialty information pending' }}<br>{{ $doctor->profile ?: 'Profile details pending confirmation' }}</p><a class="btn btn-soft btn-small" href="{{ url('/find-doctor/'.$doctor->id) }}">View profile →</a></div></article>
            @empty
                <div class="card empty">Doctor profiles will appear here when the hospital adds them.</div>
            @endforelse
        </div>
        <div class="disclosure" style="margin-top:18px">Doctor names, credentials, and chamber schedules must be confirmed by the hospital before patients rely on them. Listings from other organizations do not establish affiliation with Logikaal healthcare.</div>
    </section>

    <section class="section">
        <div class="section-head"><div><span class="eyebrow">Diagnostics</span><h2>Tests &amp; health checks</h2><p class="muted">Explore listed services, room information, and published offers.</p></div><a class="btn btn-outline btn-small" href="{{ url('/tests') }}">View test prices →</a></div>
        <div class="grid">
            <article class="card"><span class="specialty-icon" aria-hidden="true">⌕</span><h3>Diagnostic tests</h3><p class="muted">Browse the catalogue, find room details, and check listed prices.</p><a href="{{ url('/tests') }}">Explore {{ $testCount }} listed services →</a></article>
            <article class="card"><span class="specialty-icon" aria-hidden="true">◷</span><h3>Current offers</h3><p class="muted">See any active discount, validity dates, and payable price.</p><a href="{{ url('/tests') }}">Check published offers →</a></article>
            <article class="card"><span class="specialty-icon" aria-hidden="true">♡</span><h3>Health checks</h3><p class="muted">Review example package formats and confirm actual services and prices with the hospital.</p><a href="{{ url('/health-packages') }}">Explore health packages →</a></article>
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div><span class="eyebrow">A simple process</span><h2>Appointments, step by step</h2><p class="muted">Choose a specialty, request an open time, and check chamber updates before your visit.</p></div><a class="btn" href="{{ url('/appointment-info') }}">Appointment information →</a></div>
        <div class="grid">
            <article class="card"><span class="badge">01 · Explore</span><h3 style="margin-top:15px">Choose a department</h3><p class="muted">Compare doctor profiles and published schedules.</p></article>
            <article class="card"><span class="badge">02 · Book</span><h3 style="margin-top:15px">Select an available time</h3><p class="muted">Sign in or create a patient account to request a slot.</p></article>
            <article class="card"><span class="badge">03 · Visit</span><h3 style="margin-top:15px">Check live updates</h3><p class="muted">See any doctor-announced delay before travelling.</p></article>
        </div>
    </section>

    <section class="section"><div class="grid">
        <article class="card"><span class="eyebrow">Plan ahead</span><h2 style="font-size:24px;margin-top:8px">Patient &amp; visitor guide</h2><p class="muted">Learn what to prepare, how to check in, and where to ask about visitor arrangements.</p><a class="btn btn-soft btn-small" href="{{ url('/patient-guide') }}">Read the guide →</a></article>
        <article class="card visit-card"><span class="eyebrow">We are here to help</span><h2 style="font-size:24px;margin-top:8px">Visit us in Rajpara</h2><p class="muted">Laxmipur, Rajpara, Rajshahi 6000, Bangladesh</p><a class="btn btn-small" href="{{ url('/contact') }}">Contact &amp; directions →</a></article>
    </div></section>
</div>
@endsection
