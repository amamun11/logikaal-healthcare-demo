<?php
namespace App\Http\Controllers;
use App\Models\{Doctor, Appointment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChamberStatusController extends Controller
{
    public function show(Doctor $doctor) {
        abort_unless(in_array(auth()->user()->group, ['Admin','Patient','Doctor']),403);
        $fresh = $doctor->live_updated_at && $doctor->live_updated_at->isToday();
        return response()->json([
            'status'=>$fresh ? $doctor->live_status : 'not_started',
            'serial'=>$fresh ? $doctor->current_serial : null,
            'note'=>$fresh ? $doctor->live_note : null,
            'expected_return'=>$fresh ? $doctor->expected_return?->format('h:i A') : null,
            'updated_at'=>$doctor->live_updated_at?->format('d M, h:i A'),
        ])->header('Cache-Control','no-store');
    }
    public function update(Request $request) {
        abort_unless(auth()->user()->group === 'Doctor',403);
        $doctor = Doctor::where('user_id',auth()->id())->firstOrFail();
        $data = $request->validate([
            'live_status'=>['required',Rule::in(['available','consulting','break','delayed','finished'])],
            'appointment_id'=>'nullable|integer|required_if:live_status,consulting',
            'live_note'=>'nullable|string|max:250',
            'return_minutes'=>'nullable|string|max:60',
        ]);
        $duration = trim(strtolower($data['return_minutes'] ?? ''));
        $minutes = null;
        if ($duration !== '') {
            if (preg_match('/^(\d+(?:\.\d+)?)\s*(minutes?|mins?|m|hours?|hrs?|h)?$/', $duration, $parts)) {
                $unit = $parts[2] ?? 'minutes';
                $value = (float) $parts[1] * (str_starts_with($unit, 'h') ? 60 : 1);
                if ($value >= 1 && $value <= 480 && floor($value) === $value) {
                    $minutes = (int) $value;
                }
            }
            if ($minutes === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'return_minutes'=>'Enter 45, 45 minutes, or 1 hour (1–480 minutes).',
                ]);
            }
        }
        $data['return_minutes'] = $minutes;
        DB::transaction(function () use($doctor,$data) {
            $locked=Doctor::whereKey($doctor->id)->lockForUpdate()->firstOrFail();
            $serial=$locked->live_updated_at?->isToday() ? $locked->current_serial : null;
            if ($data['live_status']==='consulting') {
                $a=Appointment::whereKey($data['appointment_id'])->where('doctor',$doctor->id)
                    ->where('date',now()->toDateString())->whereNotIn('status',['cancelled','no show','completed'])->firstOrFail();
                $serial=Appointment::where('doctor',$doctor->id)->where('date',$a->date)->where('id','<=',$a->id)->count();
            }
            $locked->update(['live_status'=>$data['live_status'],'current_serial'=>$serial,
                'live_note'=>$data['live_note'] ?? null,'live_updated_at'=>now(),
                'expected_return'=>in_array($data['live_status'],['break','delayed']) && !empty($data['return_minutes']) ? now()->addMinutes((int) $data['return_minutes']) : null]);
        });
        return back()->with('success','Live chamber status updated for patients and administrators.');
    }
}
