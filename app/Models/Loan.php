<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'name', 'purpose', 'loan_date', 'total_amount', 'notes', 'is_paid'
    ];

    protected $casts = [
        'loan_date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->repayments()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
}
