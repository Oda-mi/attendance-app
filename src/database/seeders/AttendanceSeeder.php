<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

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
        $endDate   =  Carbon::today();

        foreach ($users as $user) {
            $date = $startDate->copy();

             // ランダムで休日作成
            $allDates = [];
            while ($date->lte($endDate)) {
                $allDates[] = $date->copy();
                $date->addDay();
            }

            $holidayDates = collect($allDates)->random(min(8, count($allDates)));

            foreach ($allDates as $date) {

                // 休日はスキップ
                if ($holidayDates->contains($date)) continue;

                // 勤怠レコード作成
                $attendance = Attendance::factory()->create([
                    'user_id'   => $user->id,
                    'work_date' => $date->toDateString(),
                ]);

                // 休憩データ
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
