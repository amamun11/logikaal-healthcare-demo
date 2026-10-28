<?php

namespace App\Services;

use App\Models\{Appointment, Doctor, Patient};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortalBooking
{
    public function slots(Doctor $doctor, string $date): array
    {
        $day = Carbon::parse($date)->startOfDay();
        if (! $doctor->accepting_bookings || ! $doctor->chamber_start || ! $doctor->chamber_end
            || ! in_array($day->dayOfWeek, $doctor->working_days ?? [])) {
            return [];
        }
        $start = Carbon::parse($date.' '.$doctor->chamber_start);
        $close = Carbon::parse($date.' '.$doctor->chamber_end);
        $bookings = Appointment::where('doctor', $doctor->id)
            ->whereNotIn('status', ['cancelled', 'no show'])
            ->where('s_time', '<', $close->toDateTimeString())
            ->where('e_time', '>', $start->toDateTimeString())->get();
        $slots = [];
        $minutes = max(5, (int) $doctor->slot_minutes);
        for ($time = $start->copy(); $time->copy()->addMinutes($minutes)->lte($close); $time->addMinutes($minutes)) {
            $end = $time->copy()->addMinutes($minutes);
            if ($time->isPast()) { continue; }
            $busy = $bookings->contains(fn ($a) => $a->s_time < $end->toDateTimeString() && $a->e_time > $time->toDateTimeString());
            if (! $busy) { $slots[$time->format('H:i')] = $time->format('h:i A').' – '.$end->format('h:i A'); }
        }
        return $slots;
    }

    public function book(Patient $patient, int $doctorId, string $date, string $time, string $remarks): Appointment
    {
        return DB::transaction(function () use ($patient, $doctorId, $date, $time, $remarks) {
            // Lock the parent even when no appointment rows exist yet.
            $doctor = Doctor::whereKey($doctorId)->lockForUpdate()->firstOrFail();
            if (! array_key_exists($time, $this->slots($doctor, $date))) {
                throw ValidationException::withMessages(['start' => 'This slot is no longer available. Please choose another time.']);
            }
            $start = Carbon::parse($date.' '.$time);
            $end = $start->copy()->addMinutes($doctor->slot_minutes);
            return Appointment::create([
                'patient' => $patient->id, 'doctor' => $doctor->id, 'date' => $date,
                'time_slot' => $start->format('H:i').' - '.$end->format('H:i'),
                's_time' => $start->toDateTimeString(), 'e_time' => $end->toDateTimeString(),
                's_time_key' => $start->format('YmdHi'), 'status' => 'scheduled',
                'remarks' => $remarks, 'add_date' => now()->toDateString(),
                'registration_time' => now()->toDateTimeString(), 'user' => (string) auth()->id(), 'request' => 'patient portal',
            ]);
        }, 3);
    }
}
