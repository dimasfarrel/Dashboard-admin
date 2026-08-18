<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tenant_id', 'room_id', 'amount', 'period_month', 'period_year',
        'paid_at', 'due_date', 'status', 'payment_method', 'receipt_photo', 'notes'
    ];

    protected $casts = [
        'paid_at'   => 'date',
        'due_date'  => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getPeriodLabelAttribute()
    {
        $months = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
        ];
        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }
}
