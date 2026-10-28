@extends('portal.layout')
@section('title', 'Find a doctor')
@section('content')
<h1>Find your doctor</h1><p class="muted">Choose a department, review the doctor’s profile and select a chamber time.</p>
<form class="card row" method="get"><div><label for="q">Doctor name</label><input id="q" name="q" value="{{ request('q') }}"></div><div><label for="department">Department</label><select id="department" name="department"><option value="">All departments</option>@foreach($departments as $department)<option @selected(request('department') === $department)>{{ $department }}</option>@endforeach</select></div><button>Search</button></form>
<div class="grid">@forelse($doctors as $doctor)<article class="card">
@if($doctor->img_url)<img class="photo" src="{{ asset('storage/'.$doctor->img_url) }}" alt="{{ $doctor->name }}">@endif
<h2>{{ $doctor->name }}</h2>@include('portal.schedule')<p><a class="btn" href="{{ url('/portal/doctors/'.$doctor->id) }}">Profile &amp; appointments →</a></p></article>
@empty<div class="card empty">No doctors match this search. Try another department.</div>@endforelse</div>
@include('portal.pagination', ['items'=>$doctors])
@endsection
