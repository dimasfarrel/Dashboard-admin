<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'name', 'nickname', 'nik', 'phone_wa',
        'emergency_contact_name', 'emergency_contact_phone',
        'occupation', 'origin_city', 'gender', 'birth_date',
        'start_date', 'end_date', 'ktp_photo', 'selfie_photo',
        'status', 'notes'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(TenantFieldValue::class);
    }

    /**
     * Get a specific custom field value by key
     */
    public function getCustomFieldValue(string $key): ?string
    {
        return $this->fieldValues->firstWhere('field_key', $key)?->value;
    }
}
