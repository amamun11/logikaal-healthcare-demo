@extends('portal.layout')
@section('title', 'Manage tests and prices')
@section('content')
<h1>Tests &amp; prices</h1><p class="muted">Add the hospital's real test list and prices. A discount is visible only during its start and end dates, Bangladesh time.</p>
<section class="card"><h2>Add a test</h2><form method="post" action="{{ url('/portal/admin/tests') }}">@csrf
<input type="hidden" name="discount_percent" value="0"><div class="grid"><div><label>Test name</label><input name="category" maxlength="100" required value="{{ old('category') }}"></div><div><label>Price (৳)</label><input type="number" name="price" min="0" max="99999999.99" step="0.01" required value="{{ old('price') }}"></div><div><label>Description</label><input name="description" maxlength="100" value="{{ old('description') }}"></div></div>
<div class="grid"><div><label>Floor</label><input name="test_floor" maxlength="100" value="{{ old('test_floor') }}"></div><div><label>Room</label><input name="test_room" maxlength="100" value="{{ old('test_room') }}"></div></div>
<label>Reference range (optional)</label><input name="reference_value" maxlength="1000" value="{{ old('reference_value') }}"><button style="margin-top:16px">Add to test list</button></form></section>
<h2>Current catalogue · {{ $tests->count() }} tests</h2>
@forelse($tests as $test)
<section class="card"><h3>{{ $test->category }} <span class="badge">@if($test->price !== null)Base price ৳{{ number_format((float)$test->price,2) }}@else Price not set @endif</span></h3>
<form method="post" action="{{ url('/portal/admin/tests/'.$test->id) }}">@csrf<input type="hidden" name="_method" value="PUT">
<div class="grid"><div><label>Test name</label><input name="category" maxlength="100" required value="{{ $test->category }}"></div><div><label>Base price (৳)</label><input type="number" name="price" min="0" max="99999999.99" step="0.01" required value="{{ $test->price }}"></div><div><label>Discount (%) · 0 clears offer</label><input type="number" name="discount_percent" min="0" max="100" step="0.01" required value="{{ $test->discount_percent }}"></div><div><label>Discount starts</label><input type="date" name="discount_starts_at" value="{{ $test->discount_starts_at?->format('Y-m-d') }}"></div><div><label>Discount ends</label><input type="date" name="discount_ends_at" value="{{ $test->discount_ends_at?->format('Y-m-d') }}"></div></div>
<label>Description</label><input name="description" maxlength="100" value="{{ $test->description }}">
<div class="grid"><div><label>Floor</label><input name="test_floor" maxlength="100" value="{{ $test->test_floor }}"></div><div><label>Room</label><input name="test_room" maxlength="100" value="{{ $test->test_room }}"></div></div>
<label>Reference range (optional)</label><input name="reference_value" maxlength="1000" value="{{ $test->reference_value }}">
<label>Offer note (optional)</label><input name="discount_note" maxlength="160" placeholder="Today only" value="{{ $test->discount_note }}">
@if((float)$test->discount_percent > 0)<p class="muted">Offer dates: {{ $test->discount_starts_at?->format('d M Y') ?? 'not set' }} – {{ $test->discount_ends_at?->format('d M Y') ?? 'not set' }}. {{ $test->discountIsActive() ? 'Currently visible to patients.' : 'Not currently active.' }}</p>@endif
<button>Save test, price &amp; offer</button></form></section>
@empty<p class="card empty">Add each real test name and hospital price above.</p>@endforelse
<p><a href="{{ url('/tests') }}">Preview the patient test list →</a></p>
@endsection
