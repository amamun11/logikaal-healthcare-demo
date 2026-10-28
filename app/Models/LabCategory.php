<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LabCategory extends Model
{
    use HasFactory;

    protected $table = 'lab_category';
    public $timestamps = false;
    protected $casts = [
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_starts_at' => 'date:Y-m-d',
        'discount_ends_at' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'category',
        'description',
        'reference_value',
        'procedure_id',
        'price', 'test_floor', 'test_room',
        'discount_percent',
        'discount_starts_at',
        'discount_ends_at',
        'discount_note',
    ];

    public function discountIsActive(?Carbon $on = null): bool
    {
        if ((float) $this->discount_percent <= 0 || ! $this->discount_starts_at || ! $this->discount_ends_at) {
            return false;
        }

        $day = ($on ?? Carbon::now('Asia/Dhaka'))->copy()->timezone('Asia/Dhaka')->startOfDay();

        return $day->betweenIncluded(
            Carbon::parse($this->discount_starts_at, 'Asia/Dhaka')->startOfDay(),
            Carbon::parse($this->discount_ends_at, 'Asia/Dhaka')->startOfDay()
        );
    }

    public function currentPrice(?Carbon $on = null): ?string
    {
        if ($this->price === null) {
            return null;
        }

        $amount = (float) $this->price;
        if ($this->discountIsActive($on)) {
            $amount *= 1 - ((float) $this->discount_percent / 100);
        }

        return number_format($amount, 2, '.', '');
    }
}
