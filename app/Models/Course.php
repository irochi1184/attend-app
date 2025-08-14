<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['name','start_date','end_date'];

    public function students()
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role_in_course', 'student')
            ->withTimestamps();
    }

    public function instructors()
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role_in_course', 'instructor')
            ->withTimestamps();
    }

    public function sheets()
    {
        return $this->hasMany(AttendanceSheet::class);
    }
}

