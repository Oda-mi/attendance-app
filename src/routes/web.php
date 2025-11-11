<?php

use Illuminate\Support\Facades\Route;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


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

    Route::get('/attendance/list', function () {
        return view('attendance.attendance_list');
    })->name('attendance.list');



    Route::get('/attendance', function () {
    return view('attendance.attendance_index');
})->name('attendance');

Route::get('/attendance/before_work', function () {
    return view('attendance.states.attendance_before_work');
})->name('attendance.states');;

Route::get('/attendance/on_break', function () {
    return view('attendance.states.attendance_on_break');
});

Route::get('/attendance/working', function () {
    return view('attendance.states.attendance_working');
});

Route::get('/attendance/after_work', function () {
    return view('attendance.states.attendance_after_work');
});
});







Route::get('/attendance/detail/{id}', function ($id) {
    return view('attendance.attendance_detail', compact('id'));
})->name('attendance.detail');

Route::get('/stamp_correction_request/list', function () {
    return view('stamp_correction_request.request_list');
})->name('request');

/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/

// 管理者ログイン
Route::get('/admin/login', function () {
    return view('admin.auth.admin_login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->name('admin.login');



Route::middleware(['auth'])->group(function () {

    // 管理者勤怠
    Route::get('/admin/attendance/list', function () {
        return view('admin.attendance.admin_attendance_list');
    })->name('admin.attendance.list');
});







Route::prefix('admin')->group(function () {
    


    Route::get('/attendance/{id}', function ($id) {
        return view('attendance.attendance_detail', compact('id'));
    })->name('attendance.detail');

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
