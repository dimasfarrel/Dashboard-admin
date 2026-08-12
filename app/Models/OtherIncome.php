<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'category', 'amount',
        'income_date', 'period_month', 'period_year',
        'receipt_photo', 'notes'
    ];

    protected $casts = [
        'income_date' => 'date',
    ];

    protected static $categoryCache = [];

    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'parkir'    => 'Parkir',
            'laundry'   => 'Laundry',
            'listrik'   => 'Listrik Lebih',
            'lain-lain' => 'Lain-lain',
        ];
        return $labels[$this->category] ?? ucwords(str_replace('-', ' ', $this->category));
    }

    public function getCategoryIconAttribute(): string
    {
        $icons = [
            'parkir'    => 'bi-p-circle',
            'laundry'   => 'bi-basket3',
            'listrik'   => 'bi-lightning-charge',
            'lain-lain' => 'bi-three-dots',
        ];
        return $icons[$this->category] ?? 'bi-cash-coin';
    }
}
