<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceUpdateRequest extends Model
{
    use HasFactory;

        protected $fillable = [
            'user_id',
            'attendance_id',
            'work_date',
            'start_time',
            'end_time',
            'breaks',
            'note',
            'status',
        ];

        protected $casts = [
            'breaks' => 'array',
        ];


        public function user()
        {
            return $this->belongsTo(User::class);
        }

        public function attendance()
        {
            return $this->belongsTo(Attendance::class);
        }

        public function getBreakTotalAttribute()
        {
            return collect($this->breaks)->sum(function($break) {
                if(!empty($break['start_time']) && !empty($break['end_time'])){
                    return strtotime($break['end_time']) - strtotime($break['start_time']);
                }
                return 0;
            });
        }

        public function getWorkTotalAttribute()
        {
            if($this->start_time && $this->end_time){
                return strtotime($this->end_time) - strtotime($this->start_time) - $this->breakTotal;
            }
                return 0;
        }

}
