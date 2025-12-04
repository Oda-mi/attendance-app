<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceUpdateRequest;
use App\Models\User;
use App\Models\Attendance;

class AttendanceUpdateRequestFactory extends Factory
{

    protected $model = AttendanceUpdateRequest::class;

    public function definition()
    {
        $user = User::factory()->create(['is_admin' => 0]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        return [
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'work_date'     => $attendance->work_date,
            'start_time'    => $this->faker->time('H:i'),
            'end_time'      => $this->faker->time('H:i'),
            'breaks'        => [
                [
                    'start_time' => '12:00',
                    'end_time'   => '13:00',
                ]
            ],
            'note'          => '勤怠修正',
            'status'        => 'pending',
        ];
    }
}
