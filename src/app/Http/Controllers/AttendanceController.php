<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_date', $today)
                                ->first();

        if (!$attendance) {
            return view('attendance.states.attendance_before_work');
        }

        switch ($attendance->status) {
            case Attendance::STATUS_WORKING:
                return redirect()->route('attendance.working');
            case Attendance::STATUS_ON_BREAK:
                return redirect()->route('attendance.on_break');
            case Attendance::STATUS_AFTER_WORK:
                return redirect()->route('attendance.after_work');
            default:
                return redirect()->route('attendance.before_work');
        }
    }

    public function startWork(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        $today = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => Attendance::STATUS_BEFORE_WORK]
        );

        if ($attendance->status !== Attendance::STATUS_BEFORE_WORK) {
            return redirect()->route('attendance.index');
        }

        $attendance->status = Attendance::STATUS_WORKING;
        $attendance->start_time = now();
        $attendance->save();

        return redirect()->route('attendance.working');
    }
}
