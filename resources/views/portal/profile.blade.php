@extends('portal.layout')
@section('title', $doctor->name)
@section('content')
<a href="{{ url('/portal/doctors') }}">← All doctors</a><div class="grid" style="margin-top:24px"><section class="card"><h1>{{ $doctor->name }}</h1>
@if($doctor->img_url)<img class="photo" src="{{ asset('storage/'.$doctor->img_url) }}" alt="{{ $doctor->name }}">@endif
@include('portal.schedule')<h2>About the doctor</h2><div class="details">{{ $doctor->biography ?: 'Please contact the hospital for further details about this doctor.' }}</div></section>
<section class="card"><h2>Book a chamber visit</h2><form method="get"><label for="date">Appointment date</label><input id="date" type="date" name="date" value="{{ $date }}" min="{{ now()->toDateString() }}" max="{{ now()->addDays(90)->toDateString() }}" required><button style="margin-top:12px">Show available times</button></form>
@if(count($slots))<form method="post" action="{{ url('/portal/doctors/'.$doctor->id.'/book') }}">@csrf<input type="hidden" name="date" value="{{ $date }}">
<label for="start">Available time · {{ config('app.timezone') }}</label><select id="start" name="start" required>@foreach($slots as $time=>$label)<option value="{{ $time }}" @selected(old('start') === $time)>{{ $label }}</option>@endforeach</select>
<label for="remarks">Reason for visit (optional)</label><textarea id="remarks" name="remarks" maxlength="500">{{ old('remarks') }}</textarea><p class="muted">Booking for {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}.</p><button>Confirm appointment</button></form>
@else<p class="notice">No available slots on this date. Choose another chamber day or contact the hospital.</p>@endif</section></div>
@endsection
