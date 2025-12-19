<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\AttendanceUpdateRequest;

use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{

    public function requestList(Request $request)
    {
        $user = auth()->user();

        $layout = $user->is_admin
            ? 'layouts.admin'
            : 'layouts.auth';

        $sortBy = $request->input('sort_by', 'work_date');
        $sortDir = $request->input('sort_dir', 'asc');

        $activeTab = $request->input('tab', 'pending');

        $baseQuery = AttendanceUpdateRequest::with('user')
            ->when(!$user->is_admin, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy($sortBy, $sortDir);

        $pendingRequests = (clone $baseQuery)
                        ->where('status', 'pending')
                        ->get();

        $approvedRequests = (clone $baseQuery)
                        ->where('status', 'approved')
                        ->get();

        return view('stamp_correction_request.request_list',compact(
            'layout',
            'pendingRequests',
            'approvedRequests',
            'sortBy',
            'sortDir',
            'activeTab'
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
                'status'     => 'after_work',
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
