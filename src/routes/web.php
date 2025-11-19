<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;



/*
|--------------------------------------------------------------------------
| 一般ユーザー（ログイン前）
|--------------------------------------------------------------------------
*/



Route::get('/verify-email', function () {
    return view('auth.verify-email');
})->name('verification.send');

/*
|--------------------------------------------------------------------------
| 一般ユーザー（ログイン後）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::view('/attendance/before-work', 'attendance.states.attendance_before_work')->name('attendance.before_work');
    Route::view('/attendance/working', 'attendance.states.attendance_working')->name('attendance.working');
    Route::view('/attendance/on-break', 'attendance.states.attendance_on_break')->name('attendance.on_break');
    Route::view('/attendance/after-work', 'attendance.states.attendance_after_work')->name('attendance.after_work');

    Route::post('/attendance/start', [AttendanceController::class, 'startWork'])->name('attendance.start');
    Route::post('/attendance/start_break', [AttendanceController::class, 'startBreak'])->name('attendance.start_break');
    Route::post('/attendance/end_break', [AttendanceController::class, 'endBreak'])->name('attendance.end_break');
    Route::post('/attendance/end', [AttendanceController::class, 'endWork'])->name('attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/detail/{id?}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    Route::post('/attendance/update-request', [AttendanceController::class, 'storeUpdateRequest'])->name('attendance.update_request');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');
});









/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/

// 管理者ログイン
Route::get('/admin/login', function () {
    return view('admin.auth.admin_login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login');



Route::middleware(['auth'])->group(function () {

    // 管理者勤怠
    Route::get('/admin/attendance/list', function () {
        return view('admin.attendance.admin_attendance_list');
    })->name('admin.attendance.list');
});







Route::prefix('admin')->group(function () {



    Route::get('/attendance/{id}', function ($id) {
        return view('attendance.attendance_detail', compact('id'));
    });

    Route::get('/attendance/staff/{id}', function ($id) {
        return view('admin.attendance.admin_attendance_staff', compact('id'));
    })->name('admin.attendance.staff');

    // スタッフ一覧
    Route::get('/staff/list', function () {
        return view('admin.staff.admin_staff_list');
    })->name('admin.staff.list');
});

/*
|--------------------------------------------------------------------------
| 申請関連（管理者用承認画面）
|--------------------------------------------------------------------------
*/
Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function ($attendance_correct_request_id) {
    return view('stamp_correction_request.request_approve', compact('attendance_correct_request_id'));
});
