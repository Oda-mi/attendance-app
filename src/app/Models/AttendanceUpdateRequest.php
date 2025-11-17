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
}
