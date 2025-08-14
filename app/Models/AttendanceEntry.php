<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEntry extends Model
{
    protected $fillable = ['attendance_sheet_id','user_id','status'];

    public function sheet() { return $this->belongsTo(AttendanceSheet::class, 'attendance_sheet_id'); }
    public function student() { return $this->belongsTo(User::class, 'user_id'); }
}

