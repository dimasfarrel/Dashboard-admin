<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lodging extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'pic_name', 'pic_phone', 'pic_nik', 'pic_address',
        'check_in', 'check_out', 'duration_days', 'guest_count',
        'price_per_night', 'total_price', 'deposit',
        'discount', 'daily_discount', 'fixed_discount', 'custom_adjustment',
        'payment_status', 'payment_method', 'paid_at', 'status', 'guest_names', 'notes'
    ];

    protected $casts = [
        'check_in'  => 'datetime',
        'check_out' => 'datetime',
        'paid_at'   => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Calculate total price based on current fields
     */
    public function calculateTotal(): float
    {
        $basePerNight = $this->price_per_night - ($this->daily_discount ?? 0);
        $base = max(0, $basePerNight) * ($this->guest_count ?? 1) * ($this->duration_days ?? 1);
        $total = $base - ($this->fixed_discount ?? 0) - ($this->custom_adjustment ?? 0);
        return max(0, $total);
    }
}
