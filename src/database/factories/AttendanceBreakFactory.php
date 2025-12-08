<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\AttendanceBreak;
use App\Models\Attendance;

class AttendanceBreakFactory extends Factory
{
    protected $model = AttendanceBreak::class;

    public function definition()
    {
        $start = Carbon::now()->setTime(12, 0);
        $end   = Carbon::now()->setTime(13, 0);

        return [
            'attendance_id' => Attendance::factory(),
            'start_time'    => $start,
            'end_time'      => $end,
        ];
    }
}
