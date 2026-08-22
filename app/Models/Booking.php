<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'room_id', 'nik', 'phone_wa', 'gender', 'start_date', 'duration_months', 
        'total_amount', 'status', 'payment_proof', 'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
