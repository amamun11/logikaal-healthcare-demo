@extends('portal.layout')
@section('title', 'Patient registration')
@section('content')
<section class="card form"><h1>Create your account</h1><p class="muted">Register as a patient to book your first appointment.</p>
<form method="post" action="{{ url('/portal/register') }}">@csrf
@foreach(['firstname'=>'First name','lastname'=>'Last name','login'=>'Username','email'=>'Email','phone'=>'Phone number'] as $key=>$label)
<label for="{{ $key }}">{{ $label }}</label><input id="{{ $key }}" name="{{ $key }}" type="{{ $key === 'email' ? 'email' : ($key === 'phone' ? 'tel' : 'text') }}" value="{{ old($key) }}" required maxlength="{{ in_array($key,['firstname','lastname','login']) ? 20 : 100 }}">
@endforeach
<label for="password">Password (at least 10 characters)</label><input id="password" type="password" name="password" minlength="10" maxlength="72" autocomplete="new-password" required>
<label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
<button style="width:100%;margin-top:24px">Create patient account</button></form><p><a href="{{ url('/patient/login') }}">Already registered? Log in</a></p></section>
@endsection
