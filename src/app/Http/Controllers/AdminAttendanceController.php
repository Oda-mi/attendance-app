<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\AttendanceUpdateRequestForm;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceUpdateRequest;

use Carbon\Carbon;
use Carbon\CarbonPeriod;



class AdminAttendanceController extends Controller
{

    public function dailyList(Request $request)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $today = Carbon::today()->toDateString();

        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $dateString = $request->input('date', $today);

        $attendanceDate = Carbon::parse($dateString);

        $baseAttendances = Attendance::with('breaks', 'user')
                                    ->whereDate('work_date', $attendanceDate)
                                    ->orderBy(User::select('name')->whereColumn('users.id', 'attendances.user_id'))
                                    ->get();

        $pendingRequests = AttendanceUpdateRequest::where('status', 'pending')
                                                ->whereIn('attendance_id', $baseAttendances->pluck('id'))
                                                ->get()
                                                ->keyBy('attendance_id');

        $attendances = $baseAttendances->map(function ($attendance) use ($pendingRequests) {

            if ($pendingRequests->has($attendance->id)) {

                $pending = $pendingRequests->get($attendance->id);

                $pending->is_pending = true;
                return $pending;
            }
                $attendance->is_pending = false;
                return $attendance;
        });

        $prevDate = $attendanceDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $attendanceDate->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.daily_list', compact(
            'attendanceDate',
            'attendances',
            'prevDate',
            'nextDate'
        ));
    }



    public function adminAttendanceDetail(Request $request, $id = null)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $breaks = collect();
        $attendanceData = null;
        $isEditable = true;
        $user = null;

        if ($id) {
            $updateRequest = AttendanceUpdateRequest::where('attendance_id', $id)
                                                    ->latest()
                                                    ->first();

            if ($updateRequest && $updateRequest->status === 'pending') {

                $attendanceData = (object) $updateRequest->toArray();

                $isEditable = false;

                $breaks = collect(
                    is_string($updateRequest->breaks)
                    ? json_decode($updateRequest->breaks)
                    : json_decode(json_encode($updateRequest->breaks))
                );

                $user = $updateRequest->user;

            } else {

                $attendance = Attendance::with('breaks', 'user')
                                        ->findOrFail($id);

                $attendanceData = $attendance;
                $isEditable = true;

                $breaks = collect(
                    is_string($attendance->breaks)
                    ? json_decode($attendance->breaks)
                    : json_decode(json_encode($attendance->breaks))
                );

                $user = $attendance->user;
            }
        } else {

            $work_date = $request->input('date') ?? now()->format('Y-m-d');

            $user = User::findOrFail($request->input('user_id'));

            $attendanceData = new Attendance([
                'id'         => 0,
                'user_id'    => $user->id,
                'work_date'  => $work_date,
                'start_time' => null,
                'end_time'   => null,
            ]);

            $breaks = collect();
            $isEditable = true;
        }

        $layout = 'layouts.admin';

        return view('attendance.detail', compact(
            'user',
            'attendanceData',
            'breaks',
            'isEditable',
            'layout'
        ));
    }



    public function staffList()
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $staffs = User::where('is_admin', 0)->get();

        return view('admin.staff.staff_list', compact('staffs'));
    }



    public function staffMonthlyList(Request $request, $id)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $user = User::findOrFail($id);

        $today = Carbon::today()->toDateString();

        $request->validate([
            'year'  => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year  = $request->input('year', Carbon::parse($today)->year);
        $month = $request->input('month', Carbon::parse($today)->month);

        $start = Carbon::createFromDate($year, $month, 1);
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
                        ->where('user_id', $user->id)
                        ->whereYear('work_date', $year)
                        ->whereMonth('work_date', $month)
                        ->orderBy('work_date','asc')
                        ->get();

        $displayMonth = $start->format('Y/m');

        $prevMonth = $start->copy()->subMonth();
        $nextMonth = $start->copy()->addMonth();

        $period = CarbonPeriod::create($start, $end);

        $attendanceDays = collect($period)->map(function ($date) use ($attendances, $user)
        {
            $attendance = $attendances->first(function ($workDate) use ($date) {
                return Carbon::parse($workDate->work_date)->isSameDay($date);
            });

            $updateRequest = AttendanceUpdateRequest::where('user_id', $user->id)
                                                    ->whereDate('work_date', $date)
                                                    ->where('status', 'pending')
                                                    ->latest('id')
                                                    ->first();

            if($updateRequest){
                $attendance = $updateRequest;
                $attendance->is_pending = true;

            } elseif ($attendance) {
                $attendance->is_pending = false;

            } else {
                $attendance = (object)[
                    'id'         => null,
                    'work_date'  => $date->format('Y-m-d'),
                    'start_time' => null,
                    'end_time'   => null,
                    'breaks'     => collect(),
                    'breakTotal' => 0,
                    'workTotal'  => 0,
                    'is_pending' => false,
                    'user'       => $user,
                ];
            }
            return $attendance;
        });

        return view('admin.attendance.monthly_list', compact(
            'user',
            'attendanceDays',
            'displayMonth',
            'prevMonth',
            'nextMonth'
        ));
    }



    public function export(Request $request)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $request->validate([
            'user_id' => 'required|integer',
            'year'    => 'required|integer|min:2000|max:2100',
            'month'   => 'required|integer|min:1|max:12',
        ]);

        $userId = $request->input('user_id');
        $year   = $request->input('year');
        $month  = $request->input('month');

        $user = User::findOrFail($userId);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::with('breaks')
                                ->where('user_id', $userId)
                                ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                                ->orderBy('work_date', 'asc')
                                ->get()
                                ->keyBy('work_date');

        $allDates = [];
        $currentDate = $startOfMonth->copy();

        while ($currentDate->lte($endOfMonth)) {
            $allDates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $csvHeader = [
            '日付',
            '出勤時刻',
            '退勤時刻',
            '休憩時間（合計）',
            '勤務時間（合計）',
        ];

        $csvFileName = "勤務一覧_{$user->name}_{$year}-{$month}.csv";

        $handle = fopen('php://temp', 'r+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, $csvHeader);

        foreach($allDates as $date) {

            $dateString = $date;

            $attendance = $attendances->first(function ($item, $key) use ($dateString) {
                return Carbon::parse($key)->toDateString() === $dateString;
            });

            $csvDate = $dateString;

            $isAttendanceConfirmed = $attendance
                && $attendance->start_time
                && $attendance->end_time;

            $hasValidBreak = $attendance
                && $attendance->breaks->count() > 0
                && $attendance->breaks->every(function ($break) {
                    return $break->start_time && $break->end_time;
                });

            if ($isAttendanceConfirmed) {

                $start = Carbon::parse($attendance->start_time)->format('H:i');
                $end   = Carbon::parse($attendance->end_time)->format('H:i');

                $break = $attendance->breaks->count() === 0
                    ? '00:00'
                    : gmdate('H:i', $attendance->breakTotal);

                $work = gmdate('H:i', $attendance->workTotal);

            } elseif ($attendance && $attendance->start_time && $hasValidBreak) {

                $start = Carbon::parse($attendance->start_time)->format('H:i');
                $end   = '';

                $break = gmdate('H:i', $attendance->breakTotal);
                $work  = '';

            } else {
                $start = '';
                $end   = '';
                $break = '';
                $work  = '';
            }

            fputcsv($handle, [
                $csvDate,
                $start,
                $end,
                $break,
                $work,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$csvFileName}");
    }



    public function upsertAttendance(AttendanceUpdateRequestForm $request)
    {
        $admin = auth()->user();
        $validated = $request->validated();

        $attendanceId = (int) $request->input('attendanceId', 0);
        $userId = (int) $request->input('user_id');
        $workDate = $request->input('work_date');

        DB::transaction(function () use ($attendanceId, $userId, $workDate, $validated, $request, &$attendance) {

            $workDate = Carbon::parse($request->input('work_date'))->format('Y-m-d');

            $startTime = $validated['start_time']
                        ? $workDate . ' ' . $validated['start_time']
                        : null;

            $endTime = $validated['end_time']
                        ? $workDate . ' ' . $validated['end_time']
                        : null;

            if ($attendanceId === 0) {

                $attendance = Attendance::create([
                    'user_id'    => $userId,
                    'work_date'  => $workDate,
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    'status'     => 'after_work',
                    'note'       => $validated['note'],
                ]);
            } else {

                $attendance = Attendance::findOrFail($attendanceId);

                $attendance->update([
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    'note'       => $validated['note'],
                ]);
            }

            $attendance->breaks()->delete();

            $breakStarts = $request->input('break_start', []);
            $breakEnds   = $request->input('break_end', []);

            $workDateForBreak = Carbon::parse($attendance->work_date)->format('Y-m-d');

            foreach ($breakStarts as $index => $start) {

                $start = trim($start ?? '');
                $end   = trim($breakEnds[$index] ?? '');

                if ($start === '' && $end === '') continue;

                $attendance->breaks()->create([
                    'start_time' => $start ? $workDateForBreak.' '.$start : null,
                    'end_time'   => $end   ? $workDateForBreak.' '.$end   : null,
                ]);
            }
        });

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
    }

}
