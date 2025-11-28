<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{

    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'work_date'   => null,
            'start_time'  => null,
            'end_time'    => null,
            'status'      => 'after_work',
            'note'        => null,
        ];
    }


    public function configure()
    {
        return $this->afterMaking(function ($attendance) {

            // work_date が無いと生成できないのでバリデーション
            if (empty($attendance->work_date)) {
                // 例外は使わず、ここで return にしておく
                return;
            }

            // 出勤時間（8:00〜10:00）
            $startHour = $this->faker->numberBetween(8, 10);
            $attendance->start_time = Carbon::parse($attendance->work_date)
                ->setTime($startHour, 0, 0);

            // 退勤時間（17:00〜19:00）
            $endHour = $this->faker->numberBetween(17, 19);
            $attendance->end_time = Carbon::parse($attendance->work_date)
                ->setTime($endHour, 0, 0);
        });
    }

}

