<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'type', 'amount', 'description', 'date', 'notes',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->type === 'debit';
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->type === 'credit';
    }
}
