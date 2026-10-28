<?php

namespace Tests\Feature;

use App\Models\{Appointment, Doctor, Patient, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_visitor_guide_is_public_and_includes_visit_and_safety_information(): void
    {
        $this->get('/patient-guide')->assertOk()
            ->assertSee('Patient &amp; visitor guide', false)
            ->assertSee('Check in at reception')
            ->assertSee('Do not wait for an online appointment');
    }

    public function test_health_package_examples_are_public_and_marked_as_illustrative(): void
    {
        $this->get('/health-packages')->assertOk()
            ->assertSee('General Wellness Check')
            ->assertSee('Illustrative examples:')
            ->assertSee('not approved Logikaal healthcare services or rates');
    }

    public function test_live_status_is_private_writable_only_by_own_doctor_and_expires_daily(): void
    {
        $doctor = $this->doctor();
        $patient = $this->patient();
        $this->get('/portal/chamber/'.$doctor->id)->assertRedirect();
        $this->actingAs(User::find($patient->user_id))->post('/portal/chamber-status', ['live_status'=>'break'])->assertForbidden();
        $this->actingAs(User::find($doctor->user_id))->post('/portal/chamber-status', ['live_status'=>'break', 'return_minutes'=>'15', 'live_note'=>'Back soon'])->assertSessionHasNoErrors();
        $this->actingAs(User::find($patient->user_id))->getJson('/portal/chamber/'.$doctor->id)->assertOk()->assertJsonPath('status','break')->assertJsonMissing(['name'=>$patient->name]);
        $doctor->refresh()->update(['live_updated_at'=>now()->subDay()]);
        $this->getJson('/portal/chamber/'.$doctor->id)->assertJsonPath('status','not_started')->assertJsonPath('serial',null);
        $this->actingAs(User::find($doctor->user_id))->post('/portal/chamber-status', ['live_status'=>'consulting', 'appointment_id'=>999999])->assertNotFound();
    }

    public function test_break_and_delay_accept_browser_string_minutes(): void
    {
        $this->freezeTime();
        $doctor = $this->doctor();
        $this->actingAs(User::find($doctor->user_id));
        foreach (['break', 'delayed'] as $status) {
            $this->from('/portal/doctor')->post('/portal/chamber-status', [
                'live_status'=>$status, 'appointment_id'=>'', 'return_minutes'=>'45', 'live_note'=>'',
            ])->assertRedirect('/portal/doctor')->assertSessionHasNoErrors();
            $this->assertSame(now()->addMinutes(45)->format('Y-m-d H:i:s'), $doctor->fresh()->expected_return->format('Y-m-d H:i:s'));
            $this->getJson('/portal/chamber/'.$doctor->id)->assertJsonPath('status', $status)
                ->assertJsonPath('expected_return', now()->addMinutes(45)->format('h:i A'));
        }
        $this->post('/portal/chamber-status', ['live_status'=>'break', 'return_minutes'=>'abc'])
            ->assertSessionHasErrors('return_minutes');
        $this->post('/portal/chamber-status', ['live_status'=>'available', 'return_minutes'=>''])
            ->assertRedirect();
        $this->assertNull($doctor->fresh()->expected_return);
    }

    public function test_return_duration_accepts_units_and_rejects_invalid_values(): void
    {
        $this->freezeTime();
        $doctor = $this->doctor();
        $this->actingAs(User::find($doctor->user_id));
        foreach (['45 minutes'=>45, '1 hour'=>60, '1 hours'=>60, '1.5 hours'=>90, '30 min'=>30, '2 HRS'=>120] as $input=>$minutes) {
            $this->post('/portal/chamber-status', ['live_status'=>'break', 'return_minutes'=>$input])
                ->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame(now()->addMinutes($minutes)->format('Y-m-d H:i:s'), $doctor->fresh()->expected_return->format('Y-m-d H:i:s'));
        }
        foreach (['0 minutes','9 hours','-5','tomorrow','1 minute junk'] as $input) {
            $this->post('/portal/chamber-status', ['live_status'=>'break', 'return_minutes'=>$input])->assertSessionHasErrors('return_minutes');
        }
    }

    private function user(string $role, string $login): User
    {
        return User::create(['firstname'=>$login, 'lastname'=>'Test', 'login'=>$login,
            'email'=>$login.'@example.test', 'desc'=>'test', 'group'=>$role, 'password'=>bcrypt('Long-test-pass123')]);
    }

    private function doctor(string $login = 'doctorone'): Doctor
    {
        $user = $this->user('Doctor', $login);
        return Doctor::create(['name'=>$login, 'email'=>$user->email, 'phone'=>'', 'address'=>'',
            'img_url'=>'', 'department'=>'Cardiology', 'profile'=>'MBBS', 'x'=>'', 'y'=>'', 'ion_user_id'=>'',
            'user_id'=>$user->id, 'chamber_room'=>'201', 'working_days'=>[0,1,2,3,4,5,6],
            'chamber_start'=>'09:00', 'chamber_end'=>'11:00', 'slot_minutes'=>15, 'accepting_bookings'=>true]);
    }

    private function patient(string $login = 'patientone'): Patient
    {
        $user = $this->user('Patient', $login);
        return Patient::create(['name'=>$login, 'user_id'=>$user->id, 'patient_id'=>'WEB-'.$user->id,
            'img_url'=>'', 'email'=>$user->email, 'doctor'=>'', 'address'=>'', 'phone'=>'01700000000',
            'sex'=>'', 'birthdate'=>'', 'age'=>'', 'bloodgroup'=>'', 'ion_user_id'=>'', 'how_added'=>'test',
            'add_date'=>'', 'registration_time'=>'']);
    }

    private function book(Patient $patient, Doctor $doctor, string $time = '09:00')
    {
        return $this->actingAs(User::find($patient->user_id))->post('/portal/doctors/'.$doctor->id.'/book', [
            'date'=>now()->addDay()->toDateString(), 'start'=>$time, 'remarks'=>'Consultation',
        ]);
    }

    public function test_public_entry_points_and_registration_assign_only_patient_role(): void
    {
        $this->get('/')->assertOk()->assertSee('Patient login')->assertSee('Doctor login')->assertSee('Admin login');
        foreach (['patient','doctor','admin'] as $role) { $this->get('/'.$role.'/login')->assertOk(); }
        $this->post('/portal/register', ['firstname'=>'Amina', 'lastname'=>'Rahman', 'login'=>'amina',
            'email'=>'amina@example.test', 'phone'=>'01700000000', 'password'=>'Long-test-pass123',
            'password_confirmation'=>'Long-test-pass123', 'group'=>'Admin'])->assertRedirect('/portal/patient');
        $this->assertSame('Patient', User::first()->group);
        $this->assertSame(User::first()->id, Patient::first()->user_id);
        $this->get('/portal/patient')->assertOk();
        $this->get('/portal/admin')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('/settings')->assertForbidden();
    }

    public function test_logins_enforce_role_and_reject_legacy_login_for_patients(): void
    {
        $user = $this->user('Patient','patientone');
        $this->post('/doctor/login', ['login'=>$user->login,'password'=>'Long-test-pass123'])->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->post('/users/login', ['login'=>$user->login,'password'=>'Long-test-pass123'])->assertRedirect('/');
        $this->assertGuest();
        $this->post('/patient/login', ['login'=>$user->login,'password'=>'Long-test-pass123'])->assertRedirect('/portal/patient');
        $this->assertAuthenticatedAs($user);
        $this->post('/portal/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_booking_uses_own_patient_and_rejects_busy_closed_off_grid_and_past_slots(): void
    {
        $doctor = $this->doctor(); $patient = $this->patient(); $other = $this->patient('patienttwo');
        $date = now()->addDay()->toDateString();
        $this->actingAs(User::find($patient->user_id))->post('/portal/doctors/'.$doctor->id.'/book', [
            'date'=>$date,'start'=>'09:00','patient'=>$other->id,'status'=>'completed',
        ])->assertSessionHasNoErrors()->assertRedirect('/portal/patient');
        $this->assertEquals($patient->id, Appointment::first()->patient);
        $this->assertSame('scheduled', Appointment::first()->status);
        $this->book($other,$doctor)->assertSessionHasErrors('start');
        $this->book($other,$doctor,'09:07')->assertSessionHasErrors('start');
        $this->book($other,$doctor,'11:00')->assertSessionHasErrors('start');
        $this->book($other,$doctor,'09:15')->assertSessionHasNoErrors();
        $doctor->update(['accepting_bookings'=>false]);
        $this->book($other,$doctor,'09:30')->assertSessionHasErrors('start');
        $this->post('/portal/doctors/'.$doctor->id.'/book',['date'=>now()->subDay()->toDateString(),'start'=>'09:30'])->assertSessionHasErrors('date');
        $this->assertSame(2,Appointment::count());
    }

    public function test_doctor_only_sees_own_patients_even_with_forged_doctor_filter(): void
    {
        $one=$this->doctor(); $two=$this->doctor('doctortwo');
        $p1=$this->patient(); $p2=$this->patient('patienttwo');
        $this->book($p1,$one); $this->book($p2,$two);
        $date=now()->addDay()->toDateString();
        $this->actingAs(User::find($one->user_id))->get('/portal/doctor?date='.$date.'&doctor='.$two->id)
            ->assertOk()->assertSee($p1->name)->assertDontSee($p2->name);
        $this->get('/appointments')->assertForbidden();
        $this->get('/patients/'.$p2->id)->assertForbidden();
        $this->get('/search?q=patienttwo')->assertForbidden();
        $this->get('/portal/admin')->assertForbidden();
        $otherAppointment=Appointment::where('doctor',$two->id)->first();
        $this->post('/portal/appointments/'.$otherAppointment->id.'/status',['status'=>'completed'])->assertNotFound();
        $own=Appointment::where('doctor',$one->id)->first();
        $this->post('/portal/appointments/'.$own->id.'/status',['status'=>'completed'])->assertRedirect();
        $this->assertSame('completed',$own->fresh()->status);
        $this->post('/portal/appointments/'.$own->id.'/status',['status'=>'scheduled'])->assertSessionHasErrors('status');
        $this->assertSame('completed',$own->fresh()->status);
    }

    public function test_patient_sees_only_own_appointments_and_profiles_do_not_leak_names(): void
    {
        $doctor=$this->doctor(); $p1=$this->patient(); $p2=$this->patient('patienttwo');
        $this->book($p1,$doctor); $this->book($p2,$doctor,'09:15');
        $this->actingAs(User::find($p1->user_id))->get('/portal/patient')->assertOk()->assertSee($p1->name)->assertDontSee($p2->name);
        $this->get('/portal/doctors')->assertOk()->assertSee('Cardiology');
        $this->get('/portal/doctors/'.$doctor->id.'?date='.now()->addDay()->toDateString())
            ->assertOk()->assertDontSee($p2->name)->assertDontSee('value="09:00"',false)->assertSee('value="09:30"',false);
        $this->get('/portal/doctor')->assertForbidden();
        $this->post('/portal/appointments/'.Appointment::first()->id.'/status',['status'=>'completed'])->assertForbidden();
    }

    public function test_admin_sees_all_doctors_and_can_create_linked_account_and_schedule(): void
    {
        $one=$this->doctor(); $two=$this->doctor('doctortwo'); $p1=$this->patient(); $p2=$this->patient('patienttwo');
        $this->book($p1,$one); $this->book($p2,$two);
        $this->actingAs($this->user('Admin','adminone'))->get('/portal/admin?date='.now()->addDay()->toDateString())
            ->assertOk()->assertSee($p1->name)->assertSee($p2->name)->assertSee($one->name)->assertSee($two->name);
        $this->get('/portal/admin/doctors/'.$one->id)->assertOk();
        $two->update(['user_id'=>null]);
        $data=['name'=>'Dr Nadia','department'=>'Cardiology','profile'=>'MBBS, MD','biography'=>'Cardiology specialist.',
            'chamber_room'=>'202','working_days'=>[1,2,3], 'chamber_start'=>'16:00','chamber_end'=>'20:00',
            'slot_minutes'=>20,'accepting_bookings'=>1,'login'=>'newdoctor','password'=>'Long-test-pass123','password_confirmation'=>'Long-test-pass123'];
        $this->post('/portal/admin/doctors/'.$two->id,$data)->assertSessionHasNoErrors()->assertRedirect('/portal/admin');
        $this->assertSame('Doctor',User::find($two->fresh()->user_id)->group);
        $this->assertSame([1,2,3],$two->fresh()->working_days);
        $this->post('/portal/admin/doctors/'.$two->id,array_merge($data,['chamber_end'=>'15:00']))->assertSessionHasErrors('chamber_end');
    }

    public function test_closed_weekday_and_unauthenticated_booking_are_rejected(): void
    {
        $doctor=$this->doctor(); $patient=$this->patient();
        $this->post('/portal/doctors/'.$doctor->id.'/book',[])->assertRedirect('/');
        $doctor->update(['working_days'=>[(now()->addDay()->dayOfWeek+1)%7]]);
        $this->book($patient,$doctor)->assertSessionHasErrors('start');
        $this->assertSame(0,Appointment::count());
    }

    public function test_doctor_login_redirect_and_schedule_only_update_preserve_account(): void
    {
        $doctor=$this->doctor();
        $this->post('/doctor/login',['login'=>'doctorone','password'=>'Long-test-pass123'])->assertRedirect('/portal/doctor');
        $this->get('/dashboard')->assertRedirect('/portal/doctor');
        $this->get('/portal/doctor')->assertOk();
        $this->post('/portal/logout');
        $this->actingAs($this->user('Admin','scheduleadmin'));
        $userId=$doctor->user_id;
        $hash=User::find($userId)->password;
        $this->post('/portal/admin/doctors/'.$doctor->id,[
            'name'=>'Dr Updated','department'=>'Cardiology','profile'=>'MBBS','chamber_room'=>'301',
            'working_days'=>[1,2], 'chamber_start'=>'16:00','chamber_end'=>'18:00','slot_minutes'=>15,
            'accepting_bookings'=>0,'password'=>'','password_confirmation'=>'',
        ])->assertSessionHasNoErrors();
        $this->assertSame($userId,$doctor->fresh()->user_id);
        $this->assertSame($hash,User::find($userId)->password);
        $this->assertFalse($doctor->fresh()->accepting_bookings);
    }
}
