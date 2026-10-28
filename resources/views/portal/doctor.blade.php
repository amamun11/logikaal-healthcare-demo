@extends('portal.layout')
@section('title', 'My chamber')
@section('content')
<h1>{{ $doctor->name }}</h1><section class="card">@include('portal.schedule')</section>
<section class="card"><h2>Update live chamber status</h2><p class="muted">This updates today's chamber. Announce a break before leaving. Do not put patient details in the public note.</p>
<form method="post" action="{{ url('/portal/chamber-status') }}">@csrf
<label>Status</label><select name="live_status">@foreach(['available'=>'Available','consulting'=>'Seeing a patient','break'=>'On break','delayed'=>'Delayed','finished'=>'Finished for today'] as $key=>$label)<option value="{{ $key }}" @selected($doctor->live_status===$key)>{{ $label }}</option>@endforeach</select>
<label>Patient being seen (required for Seeing a patient)</label><select name="appointment_id"><option value="">Select today's patient</option>@foreach(\App\Models\Appointment::where('doctor',$doctor->id)->where('date',now()->toDateString())->orderBy('id')->get() as $visit)@if(!in_array($visit->status,['cancelled','no show','completed']))<option value="{{ $visit->id }}">Serial #{{ $loop->iteration }} · {{ $visit->time_slot }} · {{ \App\Models\Patient::find($visit->patient)?->name }}</option>@endif
@endforeach</select>
<label>Expected return after (break/delay only)</label><input type="text" name="return_minutes" maxlength="60" placeholder="45 minutes or 1 hour" value="{{ old('return_minutes') }}"><small class="muted">Examples: 45, 45 minutes, 1 hour, 1.5 hours. Maximum 8 hours.</small>
<label>Public update (optional)</label><input name="live_note" maxlength="250" placeholder="Returning shortly" value="{{ old('live_note') }}"><button style="margin-top:16px">Publish live status</button></form></section>
<h2>Your booked patients</h2><form method="get" class="row"><div style="max-width:260px"><label for="date">Chamber date</label><input id="date" type="date" name="date" value="{{ $date }}" required></div><button>View patients</button></form>
<section class="card scroll"><table><thead><tr><th>Time</th><th>Patient</th><th>Phone</th><th>Reason for visit</th><th>Status</th></tr></thead><tbody>
@forelse($appointments as $appointment)@php($patient = $patients->get($appointment->patient))<tr><td>{{ $appointment->time_slot }}</td><td>{{ $patient?->name ?? 'Patient record unavailable' }}<br><small>{{ $patient?->patient_id }}</small></td><td>{{ $patient?->phone }}</td><td>{{ $appointment->remarks }}</td><td>
@if(in_array($appointment->status, ['cancelled','no show','completed']))<span class="badge">{{ ucfirst($appointment->status) }}</span>
@else<form method="post" action="{{ url('/portal/appointments/'.$appointment->id.'/status') }}">@csrf<label class="muted" for="status-{{ $appointment->id }}">Visit status</label><select id="status-{{ $appointment->id }}" name="status">@foreach(\App\Http\Controllers\AppointmentController::STATUSES as $status)<option value="{{ $status }}" @selected($appointment->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button style="margin-top:8px">Update</button></form>@endif</td></tr>
@empty<tr><td colspan="5" class="empty">No patients booked with you for this date.</td></tr>@endforelse
</tbody></table>@include('portal.pagination', ['items'=>$appointments])</section>
@endsection
