<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceUpdateRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
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
        $requestData = AttendanceUpdateRequest::with(['user', 'attendance.breaks'])
                                                ->findOrFail($id);

        $breaks = $requestData->attendance ? $requestData->attendance->breaks : collect();

        return view('stamp_correction_request.request_approve', compact(
            'requestData',
            'breaks'
        ));
    }


}
