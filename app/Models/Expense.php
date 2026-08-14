<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', 'title', 'description', 'amount',
        'expense_date', 'period_month', 'period_year',
        'receipt_photo', 'notes', 'room_maintenance_id'
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    protected static $categoryCache = [];

    public function getCategoryLabelAttribute()
    {
        if (empty($this->category)) return '—';
        if (!isset(self::$categoryCache[$this->category])) {
            self::$categoryCache[$this->category] = \App\Models\ExpenseCategory::where('slug', $this->category)->first();
        }
        $cat = self::$categoryCache[$this->category];
        return $cat ? $cat->name : ucwords(str_replace('_', ' ', $this->category));
    }

    public function getCategoryIconAttribute()
    {
        if (empty($this->category)) return 'bi-cash';
        if (!isset(self::$categoryCache[$this->category])) {
            self::$categoryCache[$this->category] = \App\Models\ExpenseCategory::where('slug', $this->category)->first();
        }
        $cat = self::$categoryCache[$this->category];
        return $cat ? $cat->icon : 'bi-cash';
    }
}
