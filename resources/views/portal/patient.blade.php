@extends('portal.layout')
@section('title', 'My appointments')
@section('content')
<h1>Hello, {{ $patient->name }}</h1><p class="muted">Your appointments and visit details. Patient reference: {{ $patient->patient_id }}</p><a class="btn" href="{{ url('/portal/doctors') }}">Book an appointment</a>
<section class="card scroll"><table><thead><tr><th>Date &amp; time</th><th>Doctor</th><th>Department / room</th><th>Status</th><th>Reason for visit</th></tr></thead><tbody>
@forelse($appointments as $appointment)@php($doctor = $doctors->get($appointment->doctor))<tr><td>{{ $appointment->date }}<br>Serial #{{ \App\Models\Appointment::where('doctor',$appointment->doctor)->where('date',$appointment->date)->where('id','<=',$appointment->id)->count() }}<br>{{ $appointment->time_slot }}</td><td>{{ $doctor?->name ?? 'Contact hospital' }}</td><td>{{ $doctor?->department }}<br>Floor {{ $doctor?->chamber_floor ?: 'To be confirmed' }}<br>Room {{ $doctor?->chamber_room ?: 'To be confirmed' }}</td><td><span class="badge">{{ ucfirst($appointment->status) }}</span></td><td>{{ $appointment->remarks }}</td></tr>
@empty<tr><td colspan="5" class="empty">No appointments yet. Choose a doctor to book your first visit.</td></tr>@endforelse
</tbody></table>@include('portal.pagination', ['items'=>$appointments])</section>
<h2>Doctor live status</h2>@foreach($doctors as $doctor)<section class="card"><h3>{{ $doctor->name }}</h3>@include('portal.live-status')</section>@endforeach
@endsection
