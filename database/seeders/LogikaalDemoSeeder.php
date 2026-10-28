<?php

namespace Database\Seeders;

use App\Models\{Appointment, Doctor, LabCategory, Patient, User};
use App\Services\PortalBooking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};

class LogikaalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo data is only allowed in local/testing environments.');
        }
        $credentials = [];
        DB::transaction(function () use (&$credentials) {
            $account = function ($role, $number, $first, $last) use (&$credentials) {
                $login = strtolower($role).$number;
                $existing = User::where('login', $login)->first();
                if ($existing) {
                    if ($existing->desc !== 'Logikaal demo account' || $existing->group !== $role) {
                        throw new \RuntimeException('Username already belongs to a non-demo account: '.$login);
                    }
                    return $existing;
                }
                $password = '123456';
                $user = User::create(['firstname'=>$first, 'lastname'=>$last,
                    'login'=>$login, 'email'=>$login.'@example.test', 'desc'=>'Logikaal demo account',
                    'group'=>$role, 'password'=>Hash::make($password)]);
                $credentials[] = [$role, $login, $password];
                return $user;
            };
            foreach (['Rahman','Ahmed','Islam','Akter','Hasan','Karim'] as $i => $name) {
                $account('Admin', $i + 1, 'Admin', $name);
            }
            $doctors = [];
            foreach ([['Arif Rahman','Medicine','2','201'], ['Nadia Ahmed','Cardiology','2','202'],
                ['Farhan Islam','Orthopedics','3','301'], ['Sadia Akter','Gynecology','3','302'],
                ['Imran Hasan','Pediatrics','4','401'], ['Tania Karim','Dermatology','4','402']] as $i => [$name,$department,$floor,$room]) {
                $user = $account('Doctor', $i + 1, explode(' ', $name)[0], explode(' ', $name)[1]);
                $doctors[] = Doctor::firstOrCreate(['user_id'=>$user->id], [
                    'name'=>'Dr. '.$name, 'department'=>$department, 'profile'=>'Specialist',
                    'biography'=>'',
                    'email'=>$user->email, 'phone'=>'', 'address'=>'Logikaal healthcare', 'img_url'=>'',
                    'x'=>'', 'y'=>'', 'ion_user_id'=>'', 'chamber_floor'=>$floor, 'chamber_room'=>$room,
                    'working_days'=>[0,1,2,3,4,5,6], 'chamber_start'=>'09:00', 'chamber_end'=>'17:00',
                    'slot_minutes'=>15, 'accepting_bookings'=>true]);
            }
            $names = ['Amina Rahman','Rafi Ahmed','Nusrat Islam','Hasan Ali','Mitu Akter',
                'Sajid Khan','Rima Begum','Tanvir Hasan','Nabila Karim','Fahim Rahman',
                'Sumaiya Ahmed','Rakib Islam','Mariam Akter','Sabbir Ali','Jannat Khan'];
            foreach ($names as $i => $name) {
                [$first,$last] = explode(' ', $name);
                $user = $account('Patient', $i + 1, $first, $last);
                $patient = Patient::firstOrCreate(['user_id'=>$user->id], [
                    'name'=>$name, 'email'=>$user->email, 'phone'=>'', 'patient_id'=>'PAT-'.$user->id,
                    'img_url'=>'', 'doctor'=>'', 'address'=>'', 'sex'=>'',
                    'birthdate'=>'', 'age'=>'', 'bloodgroup'=>'', 'ion_user_id'=>'', 'how_added'=>'Logikaal demo',
                    'add_date'=>now()->toDateString(), 'registration_time'=>now()->toDateTimeString()]);
                $marker = 'Appointment '.$user->id;
                if (! Appointment::where('remarks', $marker)->exists()) {
                    $doctor = $doctors[$i % count($doctors)];
                    $date = now()->addDay()->toDateString();
                    $booking = app(PortalBooking::class);
                    $slots = $booking->slots($doctor, $date);
                    if ($slots) { $booking->book($patient, $doctor->id, $date, array_key_first($slots), $marker); }
                }
            }
            // Indicative values observed in public Rajshahi provider listings for a
            // prototype catalogue only. They are not Logikaal's official prices.
            foreach ([['CBC with ESR',700,'1','101'], ['Random Blood Sugar (RBS)',120,'1','101'],
                ['HbA1c',1000,'1','101'], ['Lipid Profile',1200,'1','101'], ['Serum Creatinine',400,'1','102'],
                ['Urine R/E',200,'1','103'], ['TSH',800,'1','102'], ['ECG',350,'Ground','G01'],
                ['Echo Cardiogram',1500,'Ground','G02'], ['CT Scan of Brain',2500,'Ground','G03'],
                ['USG of Whole Abdomen',1200,'Ground','G04'], ['X-ray Chest P/A View',400,'Ground','G05']] as $i => [$name,$price,$floor,$room]) {
                LabCategory::updateOrCreate(['category'=>$name], [
                    'description'=>'', 'reference_value'=>'',
                    'price'=>$price, 'test_floor'=>$floor, 'test_room'=>$room,
                    'discount_percent'=>$i === 0 ? 30 : 0,
                    'discount_starts_at'=>$i === 0 ? now()->toDateString() : null,
                    'discount_ends_at'=>$i === 0 ? now()->toDateString() : null,
                    'discount_note'=>$i === 0 ? 'Today only' : null]);
            }
        });
        if ($credentials) {
            $path = storage_path('app/logikaal-demo-logins.csv');
            $new = ! file_exists($path);
            $file = fopen($path, 'a');
            if (! $file) { throw new \RuntimeException('Cannot save demo login file.'); }
            if ($new) { fputcsv($file, ['Role','Username','Password'], ',', '"', ''); }
            foreach ($credentials as $row) { fputcsv($file, $row, ',', '"', ''); }
            fclose($file);
        }
        $this->command?->info('Demo data ready: 6 admins, 6 doctors, 15 patients, 12 tests. Credentials: storage/app/logikaal-demo-logins.csv');
    }
}
