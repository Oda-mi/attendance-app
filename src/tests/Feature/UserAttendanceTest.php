<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceUpdateRequest;
use Carbon\Carbon;


class UserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // ４.日時取得機能
    // ========================================
    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);
        $this->actingAs($user);

        $responseAttendancePage = $this->get(route('attendance.index'));

        $currentDate = now()->locale('ja')->translatedFormat('Y年m月d日(D)');

        $currentTime = now()->format('H:i');

        $responseAttendancePage->assertSee($currentDate);
        $responseAttendancePage->assertSee($currentTime);
    }

    // ========================================
    // ５.ステータス確認機能
    // ========================================
    /** @test */
    public function 勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $responseAttendancePage = $this->get(route('attendance.index'));

        $responseAttendancePage->assertStatus(200);
        $responseAttendancePage->assertViewIs('attendance.states.before_work');
        $responseAttendancePage->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'  => $user->id,
            'work_date'=> Carbon::today(),
            'status'   => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseWorkingPage = $this->followingRedirects()->get(route('attendance.index'));
        $responseWorkingPage->assertStatus(200);
        $responseWorkingPage->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'  => $user->id,
            'work_date'=> Carbon::today(),
            'status'   => Attendance::STATUS_ON_BREAK,
        ]);

        $this->actingAs($user);

        $responseOnBreakPage = $this->followingRedirects()->get(route('attendance.index'));
        $responseOnBreakPage->assertStatus(200);
        $responseOnBreakPage->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'  => $user->id,
            'work_date'=> Carbon::today(),
            'status'   => Attendance::STATUS_AFTER_WORK,
        ]);

        $this->actingAs($user);

        $responseAfterWorkPage = $this->followingRedirects()->get(route('attendance.index'));
        $responseAfterWorkPage->assertStatus(200);
        $responseAfterWorkPage->assertSee('退勤済');
    }

    // ========================================
    // ６.出勤機能
    // ========================================
    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $responseBeforeWorkPage = $this->get(route('attendance.index'));
        $responseBeforeWorkPage->assertStatus(200);
        $responseBeforeWorkPage->assertSee('出勤');

        $this->post(route('attendance.start'));

        $responseWorkingPage = $this->followingRedirects()->get(route('attendance.index'));
        $responseWorkingPage->assertStatus(200);
        $responseWorkingPage->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_AFTER_WORK,
        ]);

        $this->actingAs($user);

        $responseAfterWorkPage = $this->followingRedirects()->get(route('attendance.index'));
        $responseAfterWorkPage->assertStatus(200);
        $responseAfterWorkPage->assertSee('退勤済');

        // 期待挙動：画面上に「出勤」ボタンが表示されない
        // 出勤ボタンは 'attendance__button' クラスを持つため、
        // ボタンの存在可否はこのクラスで判定する
        // ※ '出勤' だとヘッダー等に含まれる可能性があるため誤検知する
        $responseAfterWorkPage->assertDontSee('attendance__button--start');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.start'));

        $responseAttendanceList = $this->get(route('attendance.list'));

        $attendance = Attendance::where('user_id', $user->id)->first();

        $responseAttendanceList->assertSee(Carbon::parse($attendance->start_time)->format('H:i'));
    }

    // ========================================
    // ７.休憩機能
    // ========================================
    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseWorkingPage = $this->get(route('status.working'));
        $responseWorkingPage->assertSee('休憩入');

        $responseStartBreak = $this->post(route('attendance.start_break'));
        $responseStartBreak->assertRedirect(route('status.on_break'));

        $responseOnBreakPage = $this->get(route('status.on_break'));
        $responseOnBreakPage->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.start_break'));
        $this->post(route('attendance.end_break'));

        $responseWorkingPage = $this->get(route('status.working'));
        $responseWorkingPage->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseStartBreak = $this->post(route('attendance.start_break'));
        $responseStartBreak->assertRedirect(route('status.on_break'));

        $responseOnBreakPage = $this->get(route('status.on_break'));
        $responseOnBreakPage->assertSee('休憩戻');

        $responseEndBreak = $this->post(route('attendance.end_break'));
        $responseEndBreak->assertRedirect(route('status.working'));

        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $responseWorkingPage = $this->get(route('status.working'));
        $responseWorkingPage->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseStartBreak1 = $this->post(route('attendance.start_break'));
        $responseEndBreak1   = $this->post(route('attendance.end_break'));

        $responseStartBreak2 = $this->post(route('attendance.start_break'));

        $responseOnBreakPage = $this->get(route('status.on_break'));
        $responseOnBreakPage->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.start_break'));
        $this->post(route('attendance.end_break'));

        $responseAttendanceList = $this->get(route('attendance.list'));

        $responseAttendanceList->assertSee(Carbon::today()->locale('ja')->translatedFormat('m/d(D)'));

        $totalBreakTime = gmdate('H:i', $attendance->fresh()->breakTotal ?? 0);
        $responseAttendanceList->assertSee($totalBreakTime);
    }

    // ========================================
    // ８.退勤機能
    // ========================================
    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseWorkingPage = $this->get(route('status.working'));
        $responseWorkingPage->assertSee('退勤');

        $responseAfterWork = $this->post(route('attendance.end'));
        $responseAfterWork->assertRedirect(route('status.after_work'));

        $responseAfterWorkPage = $this->get(route('status.after_work'));
        $responseAfterWorkPage->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.start'));
        $this->post(route('attendance.end'));

        $responseAttendanceList = $this->get(route('attendance.list'));

        $attendance = Attendance::where('user_id', $user->id)->first();

        $responseAttendanceList->assertSee(Carbon::parse($attendance->end_time)->format('H:i'));
    }

    // ========================================
    // ９.勤怠一覧情報取得機能（一般ユーザー）
    // ========================================
    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

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

        $this->actingAs($user);

        $responseAttendanceList = $this->get(route('attendance.list'));

        $responseAttendanceList->assertSee(Carbon::parse($attendanceData1->work_date)->format('m/d'));
        $responseAttendanceList->assertSee(Carbon::parse($attendanceData1->start_time)->format('H:i'));
        $responseAttendanceList->assertSee(Carbon::parse($attendanceData1->end_time)->format('H:i'));

        $responseAttendanceList->assertSee(Carbon::parse($attendanceData2->work_date)->format('m/d'));
        $responseAttendanceList->assertSee(Carbon::parse($attendanceData2->start_time)->format('H:i'));
        $responseAttendanceList->assertSee(Carbon::parse($attendanceData2->end_time)->format('H:i'));
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $this->actingAs($user);

        $responseAttendanceList = $this->get(route('attendance.list'));

        $responseAttendanceList->assertSee(Carbon::now()->format('Y/m'));
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $now       = now();
        $prevMonth = $now->copy()->subMonth()->startOfMonth();

        $attendanceDataPrevMonth = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $prevMonth->toDateString(),
            'start_time'=> $prevMonth->copy()->setTime(9,0),
            'end_time'  => $prevMonth->copy()->setTime(17,0),
        ]);

        $this->actingAs($user);

        $responseCurrentMonthPage = $this->get(route('attendance.list'));
        $responseCurrentMonthPage->assertStatus(200);

        $responsePrevMonthPage = $this->get(route('attendance.list', [
            'year'  => $prevMonth->year,
            'month' => $prevMonth->month,
        ]));

        $responsePrevMonthPage->assertSee(Carbon::parse($attendanceDataPrevMonth->work_date)->format('m/d'));
        $responsePrevMonthPage->assertSee(Carbon::parse($attendanceDataPrevMonth->start_time)->format('H:i'));
        $responsePrevMonthPage->assertSee(Carbon::parse($attendanceDataPrevMonth->end_time)->format('H:i'));
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $now       = now();
        $nextMonth = $now->copy()->addMonth()->startOfMonth();;

        $attendanceDataNextMonth = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $nextMonth->toDateString(),
            'start_time'=> $nextMonth->copy()->setTime(9,0),
            'end_time'  => $nextMonth->copy()->setTime(17,0),
        ]);

        $this->actingAs($user);

        $responseCurrentMonthPage = $this->get(route('attendance.list'));
        $responseCurrentMonthPage->assertStatus(200);

        $responseNextMonthPage = $this->get(route('attendance.list', [
            'year'  => $nextMonth->year,
            'month' => $nextMonth->month,
        ]));

        $responseNextMonthPage->assertSee(Carbon::parse($attendanceDataNextMonth->work_date)->format('m/d'));
        $responseNextMonthPage->assertSee(Carbon::parse($attendanceDataNextMonth->start_time)->format('H:i'));
        $responseNextMonthPage->assertSee(Carbon::parse($attendanceDataNextMonth->end_time)->format('H:i'));
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $responseAttendanceList = $this->get(route('attendance.list'));
        $responseAttendanceList->assertStatus(200);

        $responseAttendanceDetail = $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $responseAttendanceDetail->assertStatus(200);
        $responseAttendanceDetail->assertSee(Carbon::parse($attendanceData->work_date)->format('Y年'));
        $responseAttendanceDetail->assertSee(Carbon::parse($attendanceData->work_date)->format('m月d日'));
    }

    // ========================================
    // 10.勤怠詳細情報取得機能（一般ユーザー）
    // ========================================
    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
            'name'     => 'テスト太郎',
        ]);

        $now = now();

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $now->toDateString(),
        ]);

        $this->actingAs($user);

        $responseAttendanceDetail = $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $responseAttendanceDetail->assertStatus(200);
        $responseAttendanceDetail->assertSee($user->name);
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $now = now();

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $now->toDateString(),
        ]);

        $this->actingAs($user);

        $responseAttendanceDetail = $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $responseAttendanceDetail->assertStatus(200);
        $responseAttendanceDetail->assertSee(Carbon::parse($attendanceData->work_date)->format('Y年'));
        $responseAttendanceDetail->assertSee(Carbon::parse($attendanceData->work_date)->format('m月d日'));
    }

    /** @test */
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $now = now();

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $now->toDateString(),
            'start_time'=> $now->copy()->setTime(9,0),
            'end_time'  => $now->copy()->setTime(17,0),
        ]);

        $this->actingAs($user);

        $responseAttendanceDetail = $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $responseAttendanceDetail->assertStatus(200);
        $responseAttendanceDetail->assertSee($attendanceData->start_time->format('H:i'));
        $responseAttendanceDetail->assertSee($attendanceData->end_time->format('H:i'));
    }

    /** @test */
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $now = now();

        $attendanceData = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $now->toDateString(),
        ]);

        $breakData = AttendanceBreak::factory()->create([
            'attendance_id' => $attendanceData->id,
        ]);

        $this->actingAs($user);

        $responseAttendanceDetail = $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $responseAttendanceDetail->assertStatus(200);
        $responseAttendanceDetail->assertSee($breakData->start_time->format('H:i'));
        $responseAttendanceDetail->assertSee($breakData->end_time->format('H:i'));
    }

    // ========================================
    // 11.勤怠詳細情報修正機能（一般ユーザー）
    // ========================================
    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('attendance.update_request'), [
            'start_time' => '19:00',
            'end_time'   => '18:00',
            'note'       => '勤怠修正',
        ]);

        $response->assertSessionHasErrors('work_time');

        $errors = session('errors')->get('work_time');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $errors[0]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('attendance.update_request'), [
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
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('attendance.update_request'), [
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
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $response = $this->post(route('attendance.update_request'), [
            'start_time' => '09:00',
            'end_time'   => '18:00',
            'note'       => '',
        ]);

        $response->assertSessionHasErrors('note');

        $errors = session('errors')->get('note');
        $this->assertEquals('備考を記入してください', $errors[0]);
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $this->actingAs($user);

        $attendanceUpdateRequestData = $this->post(route('attendance.update_request'), [
            'attendance_id' => $attendanceData->id,
            'start_time'   => '09:30',
            'end_time'     => '18:00',
            'note'         => '出勤時間修正',
        ]);

        $attendanceCorrection = AttendanceUpdateRequest::where('user_id', $user->id)
                                ->latest()
                                ->first();

        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $this->actingAs($admin);

        $responseRequestApprovePage = $this->get(route('stamp_correction_request.showApproveForm', [
            'attendance_correct_request_id' => $attendanceCorrection->id,
        ]));
        $responseRequestApprovePage->assertStatus(200);
        $responseRequestApprovePage->assertSee('出勤時間修正');

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);
        $responseRequestListPage->assertSee('出勤時間修正');
    }

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData1 = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
            'start_time' => '08:00',
            'end_time'   => '17:00',
        ]);

        $attendanceData2 = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $this->actingAs($user);

        $attendanceUpdateRequestData1 = $this->post(route('attendance.update_request'), [
            'attendanceId' => $attendanceData1->id,
            'start_time'   => '08:30',
            'end_time'     => '17:00',
            'note'         => '出勤時間修正',
        ]);

        $attendanceUpdateRequestData2 = $this->post(route('attendance.update_request'), [
            'attendanceId' => $attendanceData2->id,
            'start_time'   => '09:00',
            'end_time'     => '17:00',
            'note'         => '退勤時間修正',
        ]);

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);

        $responseRequestListPage->assertViewHas('pendingRequests', function ($pendingRequests) use ($user) {
            return $pendingRequests->every(fn($request) => $request->user_id === $user->id)
                && $pendingRequests->every(fn($request) => $request->status === 'pending')
                && $pendingRequests->contains(fn($request) => $request->note === '出勤時間修正')
                && $pendingRequests->contains(fn($request) => $request->note === '退勤時間修正');
        });
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData1 = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
            'start_time' => '08:00',
            'end_time'   => '17:00',
        ]);

        $attendanceData2 = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $this->actingAs($user);

        $attendanceUpdateRequestData1 = $this->post(route('attendance.update_request'), [
            'attendanceId' => $attendanceData1->id,
            'start_time'   => '08:30',
            'end_time'     => '17:00',
            'note'         => '出勤時間修正',
        ]);

        $attendanceUpdateRequestData2 = $this->post(route('attendance.update_request'), [
            'attendanceId' => $attendanceData2->id,
            'start_time'   => '09:00',
            'end_time'     => '17:00',
            'note'         => '退勤時間修正',
        ]);

        $attendanceCorrections = AttendanceUpdateRequest::where('user_id', $user->id)
                                ->orderBy('created_at', 'asc')
                                ->get();

        $admin = User::factory()->create(['is_admin' => 1,]);

        $this->actingAs($admin);

        foreach ($attendanceCorrections as $correction) {
            $this->post(route('stamp_correction_request.approve', [
                'attendance_correct_request_id' => $correction->id,
            ]));
        }

        $this->actingAs($user);

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);

        $responseRequestListPage->assertViewHas('approvedRequests', function ($approvedRequests) use ($user) {
            return $approvedRequests->every(fn($request) => $request->user_id === $user->id)
                && $approvedRequests->every(fn($request) => $request->status === 'approved')
                && $approvedRequests->contains(fn($request) => $request->note === '出勤時間修正')
                && $approvedRequests->contains(fn($request) => $request->note === '退勤時間修正');
        });
    }

    /** @test */
    public function 各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendanceData = Attendance::factory()->create([
            'user_id'    => $user->id,
            'work_date'  => now()->toDateString(),
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]);

        $this->actingAs($user);

        $attendanceUpdateRequestData = $this->post(route('attendance.update_request'), [
            'attendanceId' => $attendanceData->id,
            'start_time'   => '09:30',
            'end_time'     => '18:00',
            'note'         => '出勤時間修正',
        ]);

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);

        $responseAttendanceDetailPage = $this->get(route('attendance.detail',['id' => $attendanceData->id]));
        $responseAttendanceDetailPage->assertStatus(200);
    }

}
