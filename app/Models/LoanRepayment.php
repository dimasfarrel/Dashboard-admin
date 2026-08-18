<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class LoanRepayment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'type', 'loan_id', 'repayment_date', 'amount', 'notes'
    ];

    protected $casts = [
        'repayment_date' => 'date',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
