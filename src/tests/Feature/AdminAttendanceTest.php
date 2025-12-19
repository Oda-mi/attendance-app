<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceUpdateRequest;


class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // 12.勤怠一覧情報取得機能（管理者）
    // ========================================
    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $userA = User::factory()->create(['is_admin' => 0,]);
        $userB = User::factory()->create(['is_admin' => 0,]);

        $today = now()->toDateString();

        $attendanceDataA = Attendance::factory()->create([
            'user_id'    => $userA->id,
            'work_date'  => $today,
            'start_time' => '08:00',
            'end_time'   => '17:00',
        ]);

        $attendanceDataB = Attendance::factory()->create([
            'user_id'    => $userB->id,
            'work_date'  => $today,
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $this->actingAs($admin);

        $responseDailyListPage = $this->get(route('admin.attendance.daily_list'));
        $responseDailyListPage->assertStatus(200);

        $responseDailyListPage->assertViewHas('attendances', function ($attendances) use ($attendanceDataA, $attendanceDataB){
            return $attendances->contains($attendanceDataA)
                && $attendances->contains($attendanceDataB);
        });
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $this->actingAs($admin);

        $currentDate = now()->format('Y/m/d');

        $responseDailyListPage = $this->get(route('admin.attendance.daily_list'));
        $responseDailyListPage->assertStatus(200);

        $responseDailyListPage->assertSee($currentDate);
    }

    /** @test */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $now       = now();
        $yesterday = $now->copy()->subDay();

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $yesterday->toDateString(),
            'start_time'=> $yesterday->copy()->setTime(9,0),
            'end_time'  => $yesterday->copy()->setTime(17,0),
        ]);

        $this->actingAs($admin);

        $responseDailyListPage = $this->get(route('admin.attendance.daily_list'));
        $responseDailyListPage->assertStatus(200);

        $responseDailyListPage = $this->get(route('admin.attendance.daily_list', [
            'date' => $yesterday->toDateString(),
        ]));
        $responseDailyListPage->assertStatus(200);

        $responseDailyListPage->assertSee($yesterday->format('Y/m/d'));
        $responseDailyListPage->assertSee(Carbon::parse($attendanceData->start_time)->format('H:i'));
        $responseDailyListPage->assertSee(Carbon::parse($attendanceData->end_time)->format('H:i'));
    }

    /** @test */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $now       = now();
        $nextDay = $now->copy()->addDay();

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $nextDay->toDateString(),
            'start_time'=> $nextDay->copy()->setTime(9,0),
            'end_time'  => $nextDay->copy()->setTime(17,0),
        ]);

        $this->actingAs($admin);

        $responseDailyListPage = $this->get(route('admin.attendance.daily_list'));
        $responseDailyListPage->assertStatus(200);

        $responseDailyListPage = $this->get(route('admin.attendance.daily_list', [
            'date' => $nextDay->toDateString(),
        ]));
        $responseDailyListPage->assertStatus(200);

        $responseDailyListPage->assertSee($nextDay->format('Y/m/d'));
        $responseDailyListPage->assertSee(Carbon::parse($attendanceData->start_time)->format('H:i'));
        $responseDailyListPage->assertSee(Carbon::parse($attendanceData->end_time)->format('H:i'));
    }

    // ========================================
    // 13.勤怠詳細情報取得・修正機能（管理者）
    // ========================================
    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'start_time'=> '09:00',
            'end_time'  => '18:00',
        ]);

        $this->actingAs($admin);

        $responseAttendanceDetailPage = $this->get(route('admin.attendance.detail',[
            'id' => $attendanceData->id
        ]));
        $responseAttendanceDetailPage->assertStatus(200);

        $responseAttendanceDetailPage->assertSee($attendanceData->work_date);
        $responseAttendanceDetailPage->assertSee($attendanceData->start_time->format('H:i'));
        $responseAttendanceDetailPage->assertSee($attendanceData->end_time->format('H:i'));
    }

    /** @test */
    public function 管理者：出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('admin.attendance.upsert'), [
            'start_time' => '19:00',
            'end_time'   => '18:00',
            'note'       => '勤怠修正',
        ]);

        $response->assertSessionHasErrors('work_time');

        $errors = session('errors')->get('work_time');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $errors[0]);
    }

    /** @test */
    public function 管理者：休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('admin.attendance.upsert'), [
            'start_time'  => '09:00',
            'end_time'    => '18:00',
            'break_start' => ['19:00'],
            'break_end'   => ['19:30'],
            'note'        => '勤怠修正',
        ]);

        $response->assertSessionHasErrors('break_start.0');

        $errors = session('errors')->get('break_start.0');
        $this->assertEquals('休憩時間が不適切な値です', $errors[0]);
    }

    /** @test */
    public function 管理者：休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('admin.attendance.upsert'), [
            'start_time'  => '09:00',
            'end_time'    => '18:00',
            'break_start' => ['13:00'],
            'break_end'   => ['18:30'],
            'note'        => '勤怠修正',
        ]);

        $response->assertSessionHasErrors('break_end.0');

        $errors = session('errors')->get('break_end.0');
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', $errors[0]);
    }

    /** @test */
    public function 管理者：備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('admin.attendance.upsert'), [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'note'       => '',
        ]);

        $response->assertSessionHasErrors('note');

        $errors = session('errors')->get('note');
        $this->assertEquals('備考を記入してください', $errors[0]);
    }

    // ========================================
    // 14.ユーザー情報取得機能（管理者）
    // ========================================
    /** @test */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $userA = User::factory()->create([
            'is_admin' => 0,
            'name'     => '一般ユーザーA',
            'email'    => 'userA@example.com',
        ]);
        $userB = User::factory()->create([
            'is_admin' => 0,
            'name'     => '一般ユーザーB',
            'email'    => 'userB@example.com',
        ]);

        $this->actingAs($admin);

        $responseStaffListPage = $this->get(route('admin.staff.list'));
        $responseStaffListPage->assertStatus(200);

        $responseStaffListPage->assertSee($userA->name);
        $responseStaffListPage->assertSee($userA->email);

        $responseStaffListPage->assertSee($userB->name);
        $responseStaffListPage->assertSee($userB->email);
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0,]);

        $now       = now();
        $yesterday = $now->copy()->subDay();
        $today     = $now->copy();

        $attendanceData1 = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => $yesterday->toDateString(),
            'start_time' => $yesterday->copy()->setTime(9,0),
            'end_time'   => $yesterday->copy()->setTime(17,0),
        ]);

        $attendanceData2 = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => $today->toDateString(),
            'start_time' => $today->copy()->setTime(9,0),
            'end_time'   => $today->copy()->setTime(17,0),
        ]);

        $this->actingAs($admin);

        $responseStaffMonthlyListPage = $this->get(route('admin.attendance.monthly_list', ['id'=>$user->id]));
        $responseStaffMonthlyListPage->assertStatus(200);

        $responseStaffMonthlyListPage->assertSee(Carbon::parse($attendanceData1->work_date)->format('m/d'));
        $responseStaffMonthlyListPage->assertSee(Carbon::parse($attendanceData1->start_time)->format('H:i'));
        $responseStaffMonthlyListPage->assertSee(Carbon::parse($attendanceData1->end_time)->format('H:i'));

        $responseStaffMonthlyListPage->assertSee(Carbon::parse($attendanceData2->work_date)->format('m/d'));
        $responseStaffMonthlyListPage->assertSee(Carbon::parse($attendanceData2->start_time)->format('H:i'));
        $responseStaffMonthlyListPage->assertSee(Carbon::parse($attendanceData2->end_time)->format('H:i'));
    }

    /** @test */
    public function 管理者：「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0,]);

        $now       = now();
        $prevMonth = $now->copy()->subMonth()->startOfMonth();

        $attendanceDataPrevMonth = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $prevMonth->toDateString(),
            'start_time'=> $prevMonth->copy()->setTime(9,0),
            'end_time'  => $prevMonth->copy()->setTime(17,0),
        ]);

        $this->actingAs($admin);

        $responseCurrentMonthPage = $this->get(route('admin.attendance.monthly_list', ['id'=>$user->id]));
        $responseCurrentMonthPage->assertStatus(200);

        $responsePrevMonthPage = $this->get(route('admin.attendance.monthly_list', [
            'id'    => $user->id,
            'year'  => $prevMonth->year,
            'month' => $prevMonth->month,
        ]));

        $responsePrevMonthPage->assertSee(Carbon::parse($attendanceDataPrevMonth->work_date)->format('m/d'));
        $responsePrevMonthPage->assertSee(Carbon::parse($attendanceDataPrevMonth->start_time)->format('H:i'));
        $responsePrevMonthPage->assertSee(Carbon::parse($attendanceDataPrevMonth->end_time)->format('H:i'));
    }

    /** @test */
    public function 管理者：「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0,]);

        $now       = now();
        $nextMonth = $now->copy()->addMonth()->startOfMonth();

        $attendanceDataNextMonth = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $nextMonth->toDateString(),
            'start_time'=> $nextMonth->copy()->setTime(9,0),
            'end_time'  => $nextMonth->copy()->setTime(17,0),
        ]);

        $this->actingAs($admin);

        $responseCurrentMonthPage = $this->get(route('admin.attendance.monthly_list', ['id'=>$user->id]));
        $responseCurrentMonthPage->assertStatus(200);

        $responseNextMonthPage = $this->get(route('admin.attendance.monthly_list', [
            'id'    => $user->id,
            'year'  => $nextMonth->year,
            'month' => $nextMonth->month,
        ]));

        $responseNextMonthPage->assertSee(Carbon::parse($attendanceDataNextMonth->work_date)->format('m/d'));
        $responseNextMonthPage->assertSee(Carbon::parse($attendanceDataNextMonth->start_time)->format('H:i'));
        $responseNextMonthPage->assertSee(Carbon::parse($attendanceDataNextMonth->end_time)->format('H:i'));
    }

    /** @test */
    public function 管理者：「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create(['is_admin' => 0,]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($admin);

        $responseStaffMonthlyListPage = $this->get(route('admin.attendance.monthly_list', ['id'=>$user->id]));
        $responseStaffMonthlyListPage->assertStatus(200);

        $responseAttendanceDetailPage = $this->get(route('admin.attendance.detail', ['id' => $attendanceData->id]));
        $responseAttendanceDetailPage->assertStatus(200);

        $responseAttendanceDetailPage->assertSee(Carbon::parse($attendanceData->work_date)->format('Y年'));
        $responseAttendanceDetailPage->assertSee(Carbon::parse($attendanceData->work_date)->format('m月d日'));

    }

    // ========================================
    // 15.勤怠情報修正機能（管理者）
    // ========================================
    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $userA = User::factory()->create([
            'is_admin' => 0,
            'name'     => '一般ユーザーA',
        ]);
        $userB = User::factory()->create([
            'is_admin' => 0,
            'name'     => '一般ユーザーB',
        ]);

        $attendanceDataA = Attendance::factory()->create([
            'user_id'    => $userA->id,
            'work_date'  => now()->toDateString(),
        ]);

        $attendanceDataB = Attendance::factory()->create([
            'user_id'    => $userB->id,
            'work_date'  => now()->toDateString(),
        ]);

        AttendanceUpdateRequest::factory()->create([
            'attendance_id' => $attendanceDataA->id,
            'user_id'       => $userA->id,
            'status'        => 'pending',
        ]);

        AttendanceUpdateRequest::factory()->create([
            'attendance_id' => $attendanceDataB->id,
            'user_id'       => $userB->id,
            'status'        => 'pending',
        ]);

        $this->actingAs($admin);

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);

        $responseRequestListPage->assertViewHas('pendingRequests', function ($pendingRequests) {
            return $pendingRequests->every(fn($request) => $request->status === 'pending');
        });

        $responseRequestListPage->assertSee('一般ユーザーA');
        $responseRequestListPage->assertSee('一般ユーザーB');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $userA = User::factory()->create([
            'is_admin' => 0,
            'name'     => '一般ユーザーA',
        ]);
        $userB = User::factory()->create([
            'is_admin' => 0,
            'name'     => '一般ユーザーB',
        ]);

        $attendanceDataA = Attendance::factory()->create([
            'user_id'    => $userA->id,
            'work_date'  => now()->toDateString(),
        ]);

        $attendanceDataB = Attendance::factory()->create([
            'user_id'    => $userB->id,
            'work_date'  => now()->toDateString(),
        ]);

        AttendanceUpdateRequest::factory()->create([
            'attendance_id' => $attendanceDataA->id,
            'user_id'       => $userA->id,
            'status'        => 'approved',
        ]);

        AttendanceUpdateRequest::factory()->create([
            'attendance_id' => $attendanceDataB->id,
            'user_id'       => $userB->id,
            'status'        => 'approved',
        ]);

        $this->actingAs($admin);

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);

        $responseRequestListPage->assertViewHas('approvedRequests', function ($approvedRequests) {
            return $approvedRequests->every(fn($request) => $request->status === 'approved');
        });

        $responseRequestListPage->assertSee('一般ユーザーA');
        $responseRequestListPage->assertSee('一般ユーザーB');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
            'name'     => 'テスト太郎',
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);


        $requestData = AttendanceUpdateRequest::factory()->create([
            'attendance_id' => $attendanceData->id,
            'user_id'       => $user->id,
            'start_time'    => '09:30',
            'end_time'      => '18:00',
            'note'          => '出勤時間修正',
        ]);

        $this->actingAs($admin);

        $responseRequestDetailPage = $this->get(route('stamp_correction_request.showApproveForm', [
            'attendance_correct_request_id' => $requestData->id]));
        $responseRequestDetailPage->assertStatus(200);

        $responseRequestDetailPage->assertSee($user->name);
        $responseRequestDetailPage->assertSee(Carbon::parse($requestData->start_time)->format('H:i'));
        $responseRequestDetailPage->assertSee(Carbon::parse($requestData->end_time)->format('H:i'));
        $responseRequestDetailPage->assertSee($requestData->note);
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
            'name'     => 'テスト太郎',
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'work_date'  => now()->toDateString(),
        ]);

        $requestData = AttendanceUpdateRequest::factory()->create([
            'attendance_id' => $attendanceData->id,
            'user_id'       => $user->id,
            'start_time'    => '09:30',
            'end_time'      => '18:00',
            'note'          => '出勤時間修正',
        ]);

        $this->actingAs($admin);

        $responseApproveFormPage = $this->get(route('stamp_correction_request.showApproveForm', [
            'attendance_correct_request_id' => $requestData->id]));
        $responseApproveFormPage->assertStatus(200);

        $responseApprove = $this->post(route('stamp_correction_request.approve', [
            'attendance_correct_request_id'=>$requestData->id]));

        $this->assertDatabaseHas('attendances', [
            'id'         => $attendanceData->id,
            'user_id'    => $user->id,
            'start_time' => Carbon::parse($attendanceData->work_date)->format('Y-m-d') . ' 09:30:00',
            'end_time'   => Carbon::parse($attendanceData->work_date)->format('Y-m-d') . ' 18:00:00',
            'note'       => '出勤時間修正',
        ]);
    }

}
