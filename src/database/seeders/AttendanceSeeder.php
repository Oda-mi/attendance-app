<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('is_admin', 0)->get();

        $startDate = Carbon::now()->subDays(30);
        $endDate   = Carbon::today();

        foreach ($users as $user) {
            $date = $startDate->copy();

            $allDates = [];
            $weekendDates = [];

            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                $allDates[] = $date->copy();

                if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    $weekendDates[] = $date->copy();
                }

                $date->addDay();
            }

            foreach ($allDates as $date) {

                if (collect($weekendDates)->contains(function ($weekendDate) use ($date) {
                    return $weekendDate->isSameDay($date);
                })) {
                    continue;
                }

                $attendance = Attendance::factory()->create([
                    'user_id'   => $user->id,
                    'work_date' => $date->toDateString(),
                ]);

                $breakStart = Carbon::parse($attendance->start_time)->addHours(rand(3,4));
                $breakEnd   = (clone $breakStart)->addMinutes(60);

                $attendance->breaks()->create([
                    'start_time' => $breakStart->toDateTimeString(),
                    'end_time'   => $breakEnd->toDateTimeString(),
                ]);
            }
        }
    }
}
