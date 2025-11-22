<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
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

        $currentDate = Carbon::createFromFormat('Y-m-d', $dateString);

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

        $breaks = collect();

        $attendance = Attendance::with('breaks','user','attendanceUpdateRequests')
                                ->findOrFail($id);

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



    public function update(AttendanceUpdateRequestForm $request, $id)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $validated = $request->validated();

        DB::transaction(function() use ($id, $validated, $request) {

            $attendance = Attendance::with('breaks')->findOrFail($id);

            if ($attendance->attendanceUpdateRequests()
                            ->where('status', 'pending')
                            ->exists()) {
                                abort(403, '承認待ちのため修正できません。');
                            }

            $workDate = date('Y-m-d', strtotime($attendance->work_date));

            $breakStarts = $request->input('break_start', []);
            $breakEnds = $request->input('break_end', []);
            $breaks = [];

            foreach ($breakStarts as $index => $start) {
                $start = trim($start ?? '');
                $end = trim($breakEnds[$index] ?? '');
                if ($start === '' && $end === '') continue;
                $breaks[] = [
                    'start_time' => $start ? $workDate.' '.$start : null,
                    'end_time'   => $end   ? $workDate.' '.$end   : null,
                ];
            }

            $attendance->update([
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'note' => $validated['note'],
            ]);

            $attendance->breaks()->delete();
            foreach($breaks as $break) {
                $attendance->breaks()->create($break);
            }
        });

        return redirect()->route('admin.attendance.detail',['id'=> $id]);
    }


}
