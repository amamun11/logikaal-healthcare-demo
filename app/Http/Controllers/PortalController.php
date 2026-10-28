<?php

namespace App\Http\Controllers;

use App\Models\{Appointment, Doctor, LabCategory, Patient, User};
use App\Services\PortalBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash};
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    private function role(string $role): void
    {
        abort_unless(auth()->user()?->group === $role, 403);
    }

    public function welcome()
    {
        $departments = Doctor::query()->whereNotNull('department')->where('department', '<>', '')
            ->select('department')->distinct()->orderBy('department')->limit(8)->pluck('department');
        $doctors = Doctor::query()->where('accepting_bookings', true)->orderBy('name')->limit(6)->get();
        $testCount = LabCategory::count();
        return view('portal.welcome', compact('departments', 'doctors', 'testCount'));
    }

    public function publicDoctors(Request $request)
    {
        $data = $request->validate(['department' => 'nullable|string|max:100', 'q' => 'nullable|string|max:100']);
        $doctors = Doctor::query()
            ->when($data['department'] ?? '', fn ($query, $department) => $query->where('department', $department))
            ->when($data['q'] ?? '', fn ($query, $term) => $query->where('name', 'like', '%'.$term.'%'))
            ->orderBy('name')->paginate(12)->withQueryString();
        $departments = Doctor::query()->whereNotNull('department')->where('department', '<>', '')
            ->select('department')->distinct()->orderBy('department')->pluck('department');
        return view('portal.public-doctors', compact('doctors', 'departments'));
    }

    public function publicDoctorProfile(Doctor $doctor)
    {
        return view('portal.public-doctor', compact('doctor'));
    }

    public function specialties()
    {
        $departments = Doctor::query()->whereNotNull('department')->where('department', '<>', '')
            ->select('department')->distinct()->orderBy('department')->get()
            ->map(fn ($row) => (object) ['name' => $row->department,
                'count' => Doctor::where('department', $row->department)->count()]);
        return view('portal.specialties', compact('departments'));
    }

    public function specialty(string $name)
    {
        $doctors = Doctor::query()->where('department', $name)->orderBy('name')->paginate(12);
        abort_if($doctors->total() === 0, 404);
        return view('portal.specialty', compact('name', 'doctors'));
    }

    public function appointmentInfo()
    {
        return view('portal.appointment-info');
    }

    public function contact()
    {
        return view('portal.contact');
    }

    public function loginForm(string $role)
    {
        abort_unless(in_array($role, ['patient', 'doctor', 'admin']), 404);
        return view('portal.login', compact('role'));
    }

    public function login(Request $request, string $role)
    {
        abort_unless(in_array($role, ['patient', 'doctor', 'admin']), 404);
        $data = $request->validate(['login' => 'required|string|max:20', 'password' => 'required|string']);
        if ($role === 'admin') {
            // Keep the existing administrator OTP flow where configured.
            if (! User::where('login', $data['login'])->where('group', 'Admin')->exists()) {
                return back()->withErrors(['login' => 'Incorrect username or password.'])->onlyInput('login');
            }
            $response = app(UsersController::class)->postSignin($request);
            if (Auth::check()) { $request->session()->regenerate(); }
            return $response;
        }
        if (! Auth::attempt($data + ['group' => ucfirst($role)])) {
            return back()->withErrors(['login' => 'Incorrect username or password for this portal.'])->onlyInput('login');
        }
        $request->session()->regenerate();
        return redirect('/portal/'.$role);
    }

    public function registerForm()
    {
        return view('portal.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:20', 'lastname' => 'required|string|max:20',
            'login' => 'required|alpha_dash:ascii|min:4|max:20|unique:users,login',
            'email' => 'required|email|max:100|unique:users,email',
            'phone' => 'required|string|max:30', 'password' => 'required|string|min:10|max:72|confirmed',
        ]);
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'firstname' => $data['firstname'], 'lastname' => $data['lastname'],
                'login' => $data['login'], 'email' => $data['email'],
                'password' => Hash::make($data['password']), 'group' => 'Patient', 'desc' => 'Patient portal',
            ]);
            Patient::create([
                'user_id' => $user->id, 'name' => $data['firstname'].' '.$data['lastname'],
                'email' => $data['email'], 'phone' => $data['phone'], 'patient_id' => 'WEB-'.$user->id,
                'img_url' => '', 'doctor' => '', 'address' => '', 'sex' => '', 'birthdate' => '',
                'age' => '', 'bloodgroup' => '', 'ion_user_id' => '', 'how_added' => 'patient portal',
                'add_date' => now()->toDateString(), 'registration_time' => now()->toDateTimeString(),
            ]);
            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();
        return redirect('/portal/patient')->with('success', 'Your patient account is ready. Choose a doctor to book your visit.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function patient()
    {
        $this->role('Patient');
        $patient = Patient::where('user_id', auth()->id())->firstOrFail();
        $appointments = Appointment::where('patient', $patient->id)->orderByDesc('s_time')->paginate(20);
        $doctors = Doctor::whereIn('id', $appointments->pluck('doctor'))->get()->keyBy('id');
        return view('portal.patient', compact('patient', 'appointments', 'doctors'));
    }

    public function doctors(Request $request)
    {
        $this->role('Patient');
        $data = $request->validate(['department' => 'nullable|string|max:100', 'q' => 'nullable|string|max:100']);
        $doctors = Doctor::query()->when($data['department'] ?? '', fn ($q, $department) => $q->where('department', $department))
            ->when($data['q'] ?? '', fn ($q, $term) => $q->where('name', 'like', '%'.$term.'%'))
            ->orderBy('name')->paginate(12)->withQueryString();
        $departments = Doctor::where('department', '<>', '')->distinct()->orderBy('department')->pluck('department');
        return view('portal.doctors', compact('doctors', 'departments'));
    }

    public function tests()
    {
        $tests = LabCategory::orderBy('category')->paginate(25);
        return view('portal.tests', compact('tests'));
    }

    public function testCatalogue()
    {
        $this->role('Admin');
        $tests = LabCategory::orderBy('category')->get();
        return view('portal.test-admin', compact('tests'));
    }

    public function createTest(Request $request)
    {
        $this->role('Admin');
        $data = $this->validatedTest($request);
        LabCategory::create($data);
        return redirect('/portal/admin/tests')->with('success', $data['category'].' added to the test and price list.');
    }

    public function updateTest(Request $request, LabCategory $test)
    {
        $this->role('Admin');
        $test->update($this->validatedTest($request, $test->id));
        return redirect('/portal/admin/tests')->with('success', $test->category.' price and discount saved.');
    }

    private function validatedTest(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:100',
            'reference_value' => 'nullable|string|max:1000',
            'procedure_id' => 'nullable|string|max:100',
            'test_floor' => 'nullable|string|max:100',
            'test_room' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'discount_starts_at' => 'nullable|date_format:Y-m-d',
            'discount_ends_at' => 'nullable|date_format:Y-m-d|after_or_equal:discount_starts_at',
            'discount_note' => 'nullable|string|max:160',
        ]);

        if (LabCategory::where('category', $data['category'])->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['category' => 'This test is already in the catalogue.']);
        }

        $percent = (float) $data['discount_percent'];
        if ($percent > 0 && (empty($data['discount_starts_at']) || empty($data['discount_ends_at']))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_ends_at' => 'Set both dates for a temporary discount.']);
        }

        $data['price'] = number_format((float) $data['price'], 2, '.', '');
        $data['discount_percent'] = number_format($percent, 2, '.', '');
        if ($percent <= 0) {
            $data['discount_starts_at'] = null;
            $data['discount_ends_at'] = null;
            $data['discount_note'] = null;
        }
        return $data;
    }

    public function doctorProfile(Request $request, Doctor $doctor, PortalBooking $booking)
    {
        $this->role('Patient');
        $data = $request->validate(['date' => 'nullable|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.now()->addDays(90)->toDateString()]);
        $date = $data['date'] ?? now()->toDateString();
        $slots = $booking->slots($doctor, $date);
        return view('portal.profile', compact('doctor', 'date', 'slots'));
    }

    public function book(Request $request, Doctor $doctor, PortalBooking $booking)
    {
        $this->role('Patient');
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.now()->addDays(90)->toDateString(),
            'start' => 'required|date_format:H:i', 'remarks' => 'nullable|string|max:500',
        ]);
        $patient = Patient::where('user_id', auth()->id())->firstOrFail();
        $booking->book($patient, $doctor->id, $data['date'], $data['start'], $data['remarks'] ?? '');
        return redirect('/portal/patient')->with('success', 'Appointment confirmed. Your doctor and chamber time are listed below.');
    }

    public function doctor(Request $request)
    {
        $this->role('Doctor');
        $doctor = Doctor::where('user_id', auth()->id())->first();
        if (! $doctor) { return response()->view('portal.unlinked', [], 403); }
        $data = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $date = $data['date'] ?? now()->toDateString();
        $appointments = Appointment::where('doctor', $doctor->id)->where('date', $date)->orderBy('s_time')->paginate(30)->withQueryString();
        $patients = Patient::whereIn('id', $appointments->pluck('patient'))->get()->keyBy('id');
        return view('portal.doctor', compact('doctor', 'appointments', 'patients', 'date'));
    }

    public function doctorStatus(Request $request, Appointment $appointment)
    {
        $this->role('Doctor');
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_unless((int) $appointment->doctor === $doctor->id, 404);
        if (in_array($appointment->status, ['cancelled', 'no show', 'completed'], true)) {
            return back()->withErrors(['status' => 'This visit is closed. Contact reception to arrange another appointment.']);
        }
        $data = $request->validate(['status' => ['required', Rule::in(AppointmentController::STATUSES)]]);
        $appointment->update($data);
        return back()->with('success', 'Appointment status updated.');
    }

    public function admin(Request $request)
    {
        $this->role('Admin');
        $data = $request->validate(['date' => 'nullable|date_format:Y-m-d', 'doctor' => 'nullable|integer|exists:doctor,id']);
        $date = $data['date'] ?? now()->toDateString();
        $doctors = Doctor::orderBy('name')->get()->keyBy('id');
        $appointments = Appointment::where('date', $date)->when($data['doctor'] ?? null, fn ($q, $id) => $q->where('doctor', $id))
            ->orderBy('s_time')->paginate(30)->withQueryString();
        $patients = Patient::whereIn('id', $appointments->pluck('patient'))->get()->keyBy('id');
        return view('portal.admin', compact('doctors', 'appointments', 'patients', 'date'));
    }

    public function manage(Doctor $doctor)
    {
        $this->role('Admin');
        $account = $doctor->user_id ? User::find($doctor->user_id) : null;
        return view('portal.manage', compact('doctor', 'account'));
    }

    public function saveDoctor(Request $request, Doctor $doctor)
    {
        $this->role('Admin');
        $data = $request->validate([
            'name' => 'required|string|max:100', 'department' => 'required|string|max:100',
            'profile' => 'required|string|max:100', 'biography' => 'nullable|string|max:3000',
            'chamber_floor' => 'nullable|string|max:100',
            'chamber_room' => 'required|string|max:100', 'working_days' => 'required|array|min:1|max:7',
            'working_days.*' => 'required|integer|between:0,6|distinct',
            'chamber_start' => 'required|date_format:H:i', 'chamber_end' => 'required|date_format:H:i|after:chamber_start',
            'slot_minutes' => 'required|integer|min:5|max:120', 'accepting_bookings' => 'nullable|boolean',
            'login' => ['nullable', 'alpha_dash:ascii', 'min:4', 'max:20', Rule::unique('users', 'login')->ignore($doctor->user_id)],
            'password' => 'nullable|string|min:10|max:72|confirmed|required_with:login',
        ]);
        if ($doctor->user_id) {
            // Existing accounts keep their username; password can be reset separately.
            unset($data['login']);
        }
        DB::transaction(function () use ($doctor, $data) {
            $locked = Doctor::whereKey($doctor->id)->lockForUpdate()->firstOrFail();
            $userId = $locked->user_id;
            if (! $userId && ! empty($data['login'])) {
                $user = User::create([
                    'firstname' => mb_substr($data['name'], 0, 20), 'lastname' => '',
                    'login' => $data['login'], 'email' => $locked->email ?: '', 'desc' => 'Doctor portal',
                    'group' => 'Doctor', 'password' => Hash::make($data['password']),
                ]);
                $userId = $user->id;
            } elseif ($userId && ! empty($data['password'])) {
                User::whereKey($userId)->where('group', 'Doctor')->update(['password' => Hash::make($data['password'])]);
            }
            unset($data['login'], $data['password']);
            $data['user_id'] = $userId;
            $data['accepting_bookings'] = (bool) ($data['accepting_bookings'] ?? false);
            $data['working_days'] = array_map('intval', $data['working_days']);
            $locked->update($data);
        });
        return redirect('/portal/admin')->with('success', 'Doctor profile, chamber schedule and login saved. Existing appointments remain unchanged.');
    }
}
