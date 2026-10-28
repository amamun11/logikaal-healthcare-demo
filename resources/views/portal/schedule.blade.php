<p><strong>{{ $doctor->department ?: 'Department not listed' }}</strong><br><span class="muted">{{ $doctor->profile ?: 'Qualifications not listed' }}</span></p>
@include('portal.live-status')
<p class="muted">Floor: {{ $doctor->chamber_floor ?: 'To be confirmed' }}<br>Room: {{ $doctor->chamber_room ?: 'To be confirmed' }}<br>
@if($doctor->chamber_start && $doctor->chamber_end)
{{ \Carbon\Carbon::parse($doctor->chamber_start)->format('h:i A') }} – {{ \Carbon\Carbon::parse($doctor->chamber_end)->format('h:i A') }}<br>
{{ collect($doctor->working_days ?? [])->map(fn($day) => ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][(int)$day])->join(', ') }} · {{ $doctor->slot_minutes }} min / visit
@else Chamber schedule not published yet @endif</p>
<span class="badge">{{ $doctor->accepting_bookings ? 'Online booking open' : 'Online booking closed' }}</span>
