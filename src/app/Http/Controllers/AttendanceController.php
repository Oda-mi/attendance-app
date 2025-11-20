<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceUpdateRequestForm;
use Illuminate\Support\Facades\DB;

use App\Models\Attendance;
use App\Models\AttendanceUpdateRequest;



use Carbon\Carbon;
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
            abort(403, 'このページにはアクセスできません。');
        }

        $isEditable = true;
        $breaks = collect();
        $attendanceData = null;

        if ($id) {
            $updateRequest = AttendanceUpdateRequest::where('attendance_id', $id)
                                                    ->where('user_id', $user->id)
                                                    ->latest()
                                                    ->first();

            if ($updateRequest && $updateRequest->status === 'pending') {

                $attendanceData = $updateRequest;
                $isEditable = false;

                $breaks = collect(
                    is_string($updateRequest->breaks)
                    ? json_decode($updateRequest->breaks)
                    : json_decode(json_encode($updateRequest->breaks))
                );


            } else {

                $attendance = Attendance::with('breaks')
                                        ->where('id', $id)
                                        ->where('user_id', $user->id)
                                        ->firstOrFail();

                $attendanceData = $attendance;
                $isEditable = true;

                $breaks = collect(
                    is_string($attendance->breaks)
                    ? json_decode($attendance->breaks)
                    : json_decode(json_encode($attendance->breaks))
                );
            }
        } else {

            $work_date = $request->input('date') ?? now()->format('Y-m-d');

            $attendanceData = new Attendance([
                'id' => 0,
                'user_id' => $user->id,
                'work_date' => $work_date,
                'start_time' => null,
                'end_time' => null,
            ]);

            $breaks = collect();
            $isEditable = true;
        }

        $user = $attendanceData->user;

        $layout = $user->is_admin ? 'layouts.admin' : 'layouts.auth';

        return view('attendance.attendance_detail', compact(
            'user',
            'attendanceData',
            'breaks',
            'isEditable',
            'layout'
        ));
    }



    public function storeUpdateRequest(AttendanceUpdateRequestForm $request)
    {

        $user = auth()->user();
        $validated = $request->validated();
        $validated['status'] = 'pending';

        $attendanceId = (int) $request->input('attendanceId', 0);

        DB::transaction(function() use ($attendanceId, $request, $user, $validated, &$attendance) {

            if ($attendanceId === 0 ) {
                $workDate = $request->input('work_date') ?? now()->format('Y-m-d');
                $attendance = Attendance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'work_date' => $workDate,
                    ],
                    [
                        'start_time' => null,
                        'end_time' => null,
                    ]
                );
            } else {
                $attendance = Attendance::findOrFail($attendanceId);
                if ($attendance->user_id !== $user->id) {
                    abort(403);
                }
                $workDate = $attendance->work_date;
            }

            $breakStarts = $request->input('break_start', []);
            $breakEnds = $request->input('break_end', []);
            $breaks = [];

            foreach ($breakStarts as $index => $start) {
                $start = trim($start ?? '');
                $end = trim($breakEnds[$index] ?? '');
                if ($start === '' && $end === '') continue;
                $breaks[] = [
                    'start_time' => $start ?: null,
                    'end_time' => $end ?: null
                ];
            }

            AttendanceUpdateRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'work_date' => $workDate,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'breaks' => $breaks,
                'note' => $validated['note'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('attendance.detail', ['id' => $attendance->id]);

    }

}
