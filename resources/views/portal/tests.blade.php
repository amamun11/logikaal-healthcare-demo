@extends('portal.layout')
@section('title', 'Tests and prices')
@section('content')
<h1>Tests &amp; prices</h1>
<p class="muted">Browse the current catalogue and any time-limited offer. Discount dates use Bangladesh time.</p>
<div class="disclosure" style="margin:18px 0">Prototype pricing is for comparison only and is not an official Logikaal healthcare rate card. Public listings in Rajshahi show different prices by provider and test method. Confirm the actual test, branch, price and offer with the hospital before publishing or booking. Reference lists: <a href="https://www.rajdoc.com/test/" target="_blank" rel="noopener noreferrer">RajDoc public test list ↗</a> · <a href="https://healthsupportrajshahi.com/diagnostic-tests-packages/" target="_blank" rel="noopener noreferrer">Health Support Rajshahi ↗</a>.</div>
<section class="card scroll"><table><thead><tr><th>Test</th><th>Description</th><th>Location</th><th>Price</th><th>Discount</th><th>Payable price</th><th>Offer dates</th></tr></thead><tbody>
@forelse($tests as $test)
@php($active = $test->discountIsActive())
<tr><td><strong>{{ $test->category }}</strong></td><td>{{ $test->description ?: '—' }}</td><td>Floor: {{ $test->test_floor ?: 'To be confirmed' }}<br>Room: {{ $test->test_room ?: 'To be confirmed' }}</td>
<td>@if($test->price !== null)৳{{ number_format((float)$test->price, 2) }}@else Price not published @endif</td>
<td>@if($active)<span class="badge">{{ rtrim(rtrim(number_format((float)$test->discount_percent,2), '0'), '.') }}% off</span>@else — @endif</td>
<td><strong>@if($test->price !== null)৳{{ number_format((float)$test->currentPrice(),2) }}@else — @endif</strong>@if($active && $test->discount_note)<br><small>{{ $test->discount_note }}</small>@endif</td>
<td>@if((float)$test->discount_percent > 0 && $test->discount_starts_at){{ $test->discount_starts_at->format('d M Y') }} – {{ $test->discount_ends_at->format('d M Y') }}@else — @endif</td></tr>
@empty<tr><td colspan="7" class="empty">The hospital hasn't published its test list yet.</td></tr>@endforelse
</tbody></table>@include('portal.pagination', ['items'=>$tests])</section>
<p class="muted">Prices and limited-time offers are managed by the hospital. Please confirm the applicable price when you visit.</p>
@endsection
