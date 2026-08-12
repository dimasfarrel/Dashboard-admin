<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantCustomField extends Model
{
    protected $fillable = ['name', 'field_key', 'type', 'is_required', 'sort_order', 'placeholder'];

    protected $casts = [
        'is_required' => 'boolean',
    ];
}
