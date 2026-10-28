<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctor';
    protected $casts = ['live_updated_at'=>'datetime', 'expected_return'=>'datetime', 'working_days' => 'array', 'accepting_bookings' => 'boolean'];
    public $timestamps = false;

    protected $fillable = [
        'live_status','current_serial','live_note','expected_return','live_updated_at',
        'user_id', 'biography', 'chamber_floor', 'chamber_room', 'working_days', 'chamber_start', 'chamber_end', 'slot_minutes', 'accepting_bookings',
        'img_url',
        'name',
        'email',
        'address',
        'phone',
        'department',
        'profile',
        'x',
        'y',
        'ion_user_id',
        'procedure_id',
    ];
}
