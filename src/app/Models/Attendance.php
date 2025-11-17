<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_BEFORE_WORK = 'before_work';
    const STATUS_WORKING = 'working';
    const STATUS_ON_BREAK = 'on_break';
    const STATUS_AFTER_WORK = 'after_work';

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'status',
        'note',
    ];

    protected $casts = [
    'work_date' => 'date',
    'start_time' => 'datetime',
    'end_time' => 'datetime',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function attendanceUpdateRequests()
    {
        return $this->hasMany(AttendanceUpdateRequest::class);
    }


    public function getBreakTotalAttribute()
    {
        return $this->breaks->sum(function($break) {
            if ($break->start_time && $break->end_time){
                return strtotime($break->end_time) - strtotime($break->start_time);
            }
            return 0;
        });
    }

    public function getWorkTotalAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return strtotime($this->end_time) - strtotime($this->start_time) - $this->breakTotal;
        }
        return 0;
    }
}
