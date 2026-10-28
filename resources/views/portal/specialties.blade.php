@extends('portal.layout')
@section('title', 'Specialities')
@section('content')
<div class="container content"><span class="eyebrow">Explore services</span><h1 style="font-size:42px">Our specialities</h1><p class="muted">Browse the departments currently represented in the doctor directory.</p><div class="grid">@forelse($departments as $department)<a class="card specialty-card" href="{{ url('/specialties/'.rawurlencode($department->name)) }}"><div><span class="specialty-icon" aria-hidden="true">✚</span><h2 style="font-size:21px">{{ $department->name }}</h2></div><span class="muted">{{ $department->count }} {{ \Illuminate\Support\Str::plural('doctor', $department->count) }} listed →</span></a>@empty<div class="card empty">No departments are listed yet.</div>@endforelse</div><div class="disclosure" style="margin-top:24px">Departments shown here are based on the current prototype directory. The hospital should confirm services and staffing before public launch.</div></div>
@endsection
