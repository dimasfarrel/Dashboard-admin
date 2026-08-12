<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantFieldValue extends Model
{
    protected $fillable = ['tenant_id', 'field_key', 'value'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
