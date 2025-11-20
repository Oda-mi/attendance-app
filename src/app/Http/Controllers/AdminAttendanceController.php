<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AdminAttendanceController extends Controller
{

    public function dailyList(Request $request)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $today = Carbon::today();

        $date = $request->input('date', $today->toDateString());

        $currentDate = Carbon::parse($date);

        $attendances = Attendance::with('breaks', 'user')
                                ->whereDate('work_date', $currentDate)
                                ->orderBy(User::select('name')->whereColumn('users.id', 'attendances.user_id'))
                                ->get();

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.admin_attendance_list', compact(
            'currentDate',
            'attendances',
            'prevDate',
            'nextDate'
        ));
    }


    public function detail(Request $request, $id)
{
    $admin = auth()->user();

    if (!$admin->is_admin) {
        abort(403, 'アクセス権限がありません。');
    }

    $isEditable = true; // 管理者は基本編集不可
    $breaks = collect();

    $attendance = Attendance::with('breaks','user')->findOrFail($id);
    $attendanceData = $attendance;

    $breaks = collect(
        is_string($attendance->breaks)
        ? json_decode($attendance->breaks)
        : json_decode(json_encode($attendance->breaks))
    );

    $user = $attendanceData->user;

    $layout = $admin->is_admin ? 'layouts.admin' : 'layouts.auth';

    return view('attendance.attendance_detail', compact(
        'user',
        'attendanceData',
        'breaks',
        'isEditable',
        'layout'
    ));
}


}
