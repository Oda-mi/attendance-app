<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceUpdateRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $pendingRequests = AttendanceUpdateRequest::where('user_id', $user->id)
                                ->where('status', 'pending')
                                ->orderBy('created_at', 'desc')
                                ->get();

        $approvedRequests = AttendanceUpdateRequest::where('user_id', $user->id)
                                ->where('status', 'approved')
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('stamp_correction_request.request_list',compact('pendingRequests', 'approvedRequests'));
    }

}
