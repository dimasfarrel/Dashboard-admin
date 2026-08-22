<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Room extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'room_number', 'floor', 'price', 'status', 'type', 'size_sqm', 'description', 'photo', 'is_published'
    ];

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'room_facility');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class)->where('status', 'active');
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function maintenances()
    {
        return $this->hasMany(RoomMaintenance::class);
    }

    public function lodgings()
    {
        return $this->hasMany(Lodging::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeLodging()
    {
        return $this->hasOne(Lodging::class)->where('status', 'active');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'available'   => ['label' => 'Tersedia', 'class' => 'badge-success'],
            'occupied'    => ['label' => 'Dihuni', 'class' => 'badge-danger'],
            'maintenance' => ['label' => 'Maintenance', 'class' => 'badge-warning'],
            default       => ['label' => $this->status, 'class' => 'badge-secondary'],
        };
    }
}
