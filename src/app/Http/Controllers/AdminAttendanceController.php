<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\AttendanceUpdateRequest;
use App\Models\User;
use App\Http\Requests\AttendanceUpdateRequestForm;


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
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $dateString = $request->input('date', $today);

        $attendanceDate = Carbon::parse($dateString);

        $attendances = Attendance::with('breaks', 'user')
                                ->whereDate('work_date', $attendanceDate)
                                ->whereHas('user', function ($query) {
                                    $query->where('is_admin', 0); // 一般ユーザーだけ
                                })
                                ->orderBy(User::select('name')->whereColumn('users.id', 'attendances.user_id'))
                                ->get();

        $prevDate = $attendanceDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $attendanceDate->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.admin_attendance_list', compact(
            'attendanceDate',
            'attendances',
            'prevDate',
            'nextDate'
        ));
    }


    public function detail(Request $request, $id = null)
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
        // 勤怠レコード取得
        $attendance = Attendance::with('breaks','user','attendanceUpdateRequests')
                                ->findOrFail($id);

        // attendance に紐付くユーザーを取得
        $user = $attendance->user;

        // 保留中の修正申請がある場合
        $pendingRequest = $attendance->attendanceUpdateRequests()
                                    ->where('status', 'pending')
                                    ->latest()
                                    ->first();

        if ($pendingRequest) {
            $attendanceData = $pendingRequest;
            $isEditable = false;

            $breaks = collect(
                is_string($pendingRequest->breaks)
                ? json_decode($pendingRequest->breaks)
                : json_decode(json_encode($pendingRequest->breaks))
            );
        } else {
            $attendanceData = $attendance;
            $isEditable = true;

            $breaks = collect(
                is_string($attendance->breaks)
                ? json_decode($attendance->breaks)
                : json_decode(json_encode($attendance->breaks))
            );
        }
    } else {
        // $id が渡されなかった場合は新規作成用
        $work_date = $request->input('date') ?? now()->format('Y-m-d');

        // hidden で送られた user_id からユーザー取得
        $user = User::findOrFail($request->input('user_id'));

        $attendanceData = new Attendance([
            'id' => 0,
            'work_date' => $work_date,
            'user_id' => $user->id,
            'start_time' => null,
            'end_time' => null,
        ]);

        $breaks = collect();
        $isEditable = true;
    }

    $layout = 'layouts.admin';

    return view('attendance.attendance_detail', compact(
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

        return view('admin.staff.admin_staff_list', compact('staffs'));
    }



    public function staffMonthlyList(Request $request, $id)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $user = User::where('is_admin', 0)->findOrFail($id);

        $today = Carbon::today()->toDateString();

        $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $request->input('year', Carbon::parse($today)->year);
        $month = $request->input('month', Carbon::parse($today)->month);

        $start = Carbon::createFromDate($year, $month, 1);
        $end = $start->copy()->endOfMonth();

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

            return $attendance ?? (object)[
                'id'         => null,
                'work_date'  => $date->format('Y-m-d'),
                'user'       => $user,
                'start_time' => null,
                'end_time'   => null,
                'breakTotal' => 0,
                'workTotal'  => 0,
            ];
        });

        return view('admin.attendance.admin_attendance_staff', compact(
            'user',
            'attendanceDays',
            'displayMonth',
            'prevMonth',
            'nextMonth'
        ));
    }


    
public function upsertAttendance(AttendanceUpdateRequestForm $request)
{
    $admin = auth()->user();
    $validated = $request->validated();

    $attendanceId = (int) $request->input('attendanceId', 0);
    $userId = (int) $request->input('user_id'); // hidden で送ってきたやつ
    $workDate = $request->input('work_date');

    DB::transaction(function () use ($attendanceId, $userId, $workDate, $validated, $request, &$attendance) {

        //  勤怠レコードを取得 or 新規作成
        if ($attendanceId === 0) {
            // 勤怠なし → 新規作成
            $attendance = Attendance::create([
                'user_id'    => $userId,
                'work_date'  => $workDate,
                'start_time' => $validated['start_time'],
                'end_time'   => $validated['end_time'],
                'note'       => $validated['note'],
            ]);
        } else {
            // 勤怠あり → 更新
            $attendance = Attendance::findOrFail($attendanceId);

            $attendance->update([
                'start_time' => $validated['start_time'],
                'end_time'   => $validated['end_time'],
                'note'       => $validated['note'],
            ]);
        }

        // 休憩の反映（毎回リセットして再登録）
        $attendance->breaks()->delete();

        $breakStarts = $request->input('break_start', []);
        $breakEnds   = $request->input('break_end', []);

        $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

        foreach ($breakStarts as $index => $start) {
            $start = trim($start ?? '');
            $end = trim($breakEnds[$index] ?? '');

            if ($start === '' && $end === '') continue;

            $attendance->breaks()->create([
                'start_time' => $start ? $workDate.' '.$start : null,
                'end_time'   => $end   ? $workDate.' '.$end   : null,
    ]);
        }
    });

    return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
}





}
