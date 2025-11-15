<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Attendance;
use Carbon\CarbonPeriod;

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



    public function startBreak(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_date', $today)
                                ->first();

        if (!$attendance || $attendance->status !== Attendance::STATUS_WORKING) {
            return redirect()->route('attendance.index');
        }

        $attendance->breaks()->create(['start_time' => now()]);

        $attendance->status = Attendance::STATUS_ON_BREAK;
        $attendance->save();

        return redirect()->route('attendance.on_break');
    }



    public function endBreak(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_date', $today)
                                ->first();

        if (!$attendance || $attendance->status !== Attendance::STATUS_ON_BREAK) {
            return redirect()->route('attendance.index');
        }

        $break = $attendance->breaks()
                            ->whereNull('end_time')
                            ->latest()
                            ->first();

        if ($break) {
            $break->end_time = now();
            $break->save();
        }

        $attendance->status = Attendance::STATUS_WORKING;
        $attendance->save();

        return redirect()->route('attendance.working');
    }



    public function endWork(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_date', $today)
                                ->first();

        if (!$attendance || $attendance->status !== Attendance::STATUS_WORKING) {
            return redirect()->route('attendance.index');
        }

        $attendance->status = Attendance::STATUS_AFTER_WORK;
        $attendance->end_time = now();
        $attendance->save();

        return redirect()->route('attendance.after_work');
    }



    public function list(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        $today = Carbon::today();

        $year = $request->input('year', $today->year);
        $month = $request->input('month', $today->month);

        $attendances = Attendance::with('breaks')
                                ->where('user_id', $user->id)
                                ->whereYear('work_date', $year)
                                ->whereMonth('work_date', $month)
                                ->orderBy('work_date','asc')
                                ->get();

        $displayMonth = Carbon::createFromDate($year, $month, 1)->format('Y/m');

        $prevMonth = Carbon::createFromDate($year, $month, 1)->subMonth();
        $nextMonth = Carbon::createFromDate($year, $month, 1)->addMonth();

        $start = Carbon::createFromDate($year, $month, 1);
        $end   = (clone $start)->endOfMonth();

        $period = CarbonPeriod::create($start, $end);

        $attendanceDays = collect($period)->map(function ($date) use ($attendances)
        {
            $attendance = $attendances->first(function ($workDate) use ($date) {
                return Carbon::parse($workDate->work_date)->isSameDay($date);
            });

            return $attendance ?? (object)[
                'id'         => null,
                'work_date'  => $date->format('Y-m-d'),
                'start_time' => null,
                'end_time'   => null,
                'breakTotal' => 0,
                'workTotal'  => 0,
            ];
        });

        return view('attendance.attendance_list',compact(
            'attendanceDays',
            'displayMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function detail(Request $request, $id = null)

    {
        $user = auth()->user();

        if ($user->is_admin) {
            abort(403,'このページにはアクセスできません。');
        }

        if ($id) {
            $attendance = Attendance::with('breaks')
                                    ->where('user_id', $user->id)
                                    ->findOrFail($id);

            $breaks = $attendance->breaks;
        } else {

            $work_date = $request->input('date') ?? now()->format('Y-m-d');

            $attendance = new Attendance([
                'id' => null,
                'user_id' => $user->id,
                'work_date' => $work_date,
                'start_time' => null,
                'end_time' => null,
            ]);

            $breaks = collect();
        }

        return view('attendance.attendance_detail',compact(
            'user',
            'attendance',
            'breaks'
        ));
    }
}
