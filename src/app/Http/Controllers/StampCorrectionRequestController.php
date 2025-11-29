<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceUpdateRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{

    public function requestList()
    {
        $user = auth()->user();

        $layout = $user->is_admin
            ? 'layouts.admin'
            : 'layouts.auth';

        if($user->is_admin){

            $pendingRequests = AttendanceUpdateRequest::with('user')
                                                        ->where('status', 'pending')
                                                        ->orderBy('work_date', 'asc')
                                                        ->get();

            $approvedRequests = AttendanceUpdateRequest::with('user')
                                                        ->where('status', 'approved')
                                                        ->orderBy('work_date', 'asc')
                                                        ->get();

        } else {

            $pendingRequests = AttendanceUpdateRequest::where('user_id', $user->id)
                                                        ->where('status', 'pending')
                                                        ->orderBy('work_date', 'asc')
                                                        ->get();

            $approvedRequests = AttendanceUpdateRequest::where('user_id', $user->id)
                                                        ->where('status', 'approved')
                                                        ->orderBy('work_date', 'asc')
                                                        ->get();
        }

        return view('stamp_correction_request.request_list',compact(
            'layout',
            'pendingRequests',
            'approvedRequests',
            ));
    }



    public function showApproveForm($id)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $requestData = AttendanceUpdateRequest::with('user', 'attendance')
                                                ->findOrFail($id);

        $breaks = $requestData->breaks ?? [];

        $breaks[] = [
            'start_time' => null,
            'end_time' => null,
        ];

        return view('stamp_correction_request.request_approve', compact(
            'requestData',
            'breaks'
        ));
    }



    public function approve($id)
    {
        $admin = auth()->user();

        if (!$admin->is_admin) {
            abort(403, 'アクセス権限がありません。');
        }

        $requestData = AttendanceUpdateRequest::with(['attendance', 'user'])
                                                    ->findOrFail($id);

        DB::transaction(function () use ($requestData) {

            $requestData->status = 'approved';
            $requestData->save();

            $attendance = $requestData->attendance;

            $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

            $startDateTime = $workDate . ' ' . $requestData->start_time;
            $endDateTime   = $workDate . ' ' . $requestData->end_time;

            $attendance->update([
                'start_time' => $startDateTime,
                'end_time'   => $endDateTime,
                'note'       => $requestData->note,
            ]);

            $attendance->breaks()->delete();

            foreach ($requestData->breaks as $break) {

                $start = isset($break['start_time'])
                        ? trim($break['start_time'])
                        : null;

                $end = isset($break['end_time'])
                    ? trim($break['end_time'])
                    : null;

                $attendance->breaks()->create([
                    'start_time' => $start ? $workDate . ' ' . $start : null,
                    'end_time'   => $end   ? $workDate . ' ' . $end   : null,
                ]);
            }
        });

        return redirect()->route('stamp_correction_request.showApproveForm', ['attendance_correct_request_id' => $requestData->id]);

    }


}
