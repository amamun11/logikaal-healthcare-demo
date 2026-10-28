@extends('portal.layout')
@section('title', ucfirst($role).' login')
@section('content')
<section class="card form"><span class="badge">{{ ucfirst($role) }} portal</span><h1 style="margin-top:18px">Welcome back</h1><p class="muted">Sign in with your {{ $role }} account.</p>
<form method="post" action="{{ url('/'.$role.'/login') }}">@csrf
<label for="login">Username</label><input id="login" name="login" value="{{ old('login') }}" autocomplete="username" maxlength="20" required>
<label for="password">Password</label><input type="password" id="password" name="password" autocomplete="current-password" required>
<button style="width:100%;margin-top:24px">Log in</button></form>
@if($role === 'patient')<p><a href="{{ url('/portal/register') }}">Create a patient account</a></p>@else<p class="muted">Account access is managed by the hospital administrator.</p>@endif
</section>
@endsection
