<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceSheet extends Model
{
    protected $fillable = ['course_id','date','locked'];
    protected $casts = [
        // 保存時/取得時ともに Y-m-d で扱う
        'date' => 'date:Y-m-d',
        'locked' => 'bool',
    ];

    // さらに保険でミューテータ（任意）
    protected function date(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Carbon::parse($value)->format('Y-m-d'),
        );
    }

    public function course() { return $this->belongsTo(Course::class); }
    public function entries() { return $this->hasMany(AttendanceEntry::class); }
}

