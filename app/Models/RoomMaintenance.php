<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class RoomMaintenance extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'room_id', 'category', 'item_name', 'description',
        'cost', 'vendor', 'vendor_phone', 'report_date', 'done_date',
        'before_photo', 'after_photo', 'status', 'notes'
    ];

    protected $casts = [
        'report_date' => 'date',
        'done_date'   => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    protected static $categoryCache = [];

    public function getCategoryLabelAttribute()
    {
        if (empty($this->category)) return '—';
        if (!isset(self::$categoryCache[$this->category])) {
            self::$categoryCache[$this->category] = \App\Models\MaintenanceCategory::where('slug', $this->category)->first();
        }
        $cat = self::$categoryCache[$this->category];
        return $cat ? $cat->name : ucwords(str_replace('_', ' ', $this->category));
    }
}
