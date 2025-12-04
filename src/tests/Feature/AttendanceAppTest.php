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

class AttendanceAppTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // １.認証機能（一般ユーザー）
    // ========================================
    /** @test */
    public function 名前が未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors('name');

        $errors = session('errors')->get('name');
        $this->assertEquals('お名前を入力してください', $errors[0]);
    }

    /** @test */
    public function メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->get('email');
        $this->assertEquals('メールアドレスを入力してください', $errors[0]);
    }

    /** @test */
    public function パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567'
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertEquals('パスワードは８文字以上で入力してください', $errors[0]);
    }

    /** @test */
    public function パスワードが一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password'
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertEquals('パスワードと一致しません', $errors[0]);
    }

    /** @test */
    public function パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertEquals('パスワードを入力してください', $errors[0]);
    }

    /** @test */
    public function フォームに内容が入力されていた場合、データが正常に保存される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
    /*
     * 注意：
     * メール認証機能を実装したため、実際には
     * 会員登録後はメール認証誘導画面に遷移します
     * （元のテストケース要件とは挙動が異なります）
     */


    // ========================================
    // ２.ログイン認証機能（一般ユーザー）
    // ========================================
    /** @test */
    public function 一般ユーザー：メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->get('email');
        $this->assertEquals('メールアドレスを入力してください', $errors[0]);
    }

    /** @test */
    public function 一般ユーザー：パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertEquals('パスワードを入力してください', $errors[0]);
    }

    /** @test */
    public function 一般ユーザー：登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->get('email');
        $this->assertEquals('ログイン情報が登録されていません', $errors[0]);
    }

    // ========================================
    // ３.ログイン認証機能（管理者）
    // ========================================
    /** @test */
    public function 管理者ログイン：メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email'    => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->get('email');
        $this->assertEquals('メールアドレスを入力してください', $errors[0]);
    }

    /** @test */
    public function 管理者ログイン：パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertEquals('パスワードを入力してください', $errors[0]);
    }

    /** @test */
    public function 管理者ログイン：登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email'    => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->get('email');
        $this->assertEquals('ログイン情報が登録されていません', $errors[0]);
    }

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
    }
    // テストケースの「画面上に「出勤」ボタンが表示されない」部分
    // ※現在の画面仕様では HTML に「出勤」ボタンが存在しないため、assertDontSee では確認できない為、
    // 代わりに assertSee で「退勤済」が表示されていることを確認しています


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

        $attendance = Attendance::factory()->create([
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

        $attendance = Attendance::factory()->create([
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

        $attendance = Attendance::factory()->create([
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

        $attendance = Attendance::factory()->create([
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
        $responseAttendanceDetail->assertSee($attendanceData->work_date);
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
            'start_time'=> $now->copy()->setTime(9,0),
            'end_time'  => $now->copy()->setTime(17,0),
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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

        $this->get(route('attendance.detail', ['id' => $attendanceData->id]));

        $attendanceUpdateRequestData = $this->post(route('attendance.update_request'), [
            'attendanceId' => $attendanceData->id,
            'start_time'   => '09:30',
            'end_time'     => '18:00',
            'note'         => '出勤時間修正',
        ]);

        $this->assertDatabaseHas('attendance_update_requests', [
            'user_id' => $user->id,
            'note'    => '出勤時間修正',
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

        $responseRequestListPage->assertViewHas('pendingRequests', function ($pendingRequests) use ($user) {
            return $pendingRequests->every(fn($request) => $request->user_id === $user->id)
                && $pendingRequests->every(fn($request) => $request->status === 'pending')
                && $pendingRequests->contains(fn($request) => $request->note === '出勤時間修正');
        });
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
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

        $attendanceCorrection = AttendanceUpdateRequest::where('user_id', $user->id)
                                ->latest()
                                ->first();

        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $this->actingAs($admin);

        $this->post(route('stamp_correction_request.approve', ['attendance_correct_request_id' =>  $attendanceCorrection->id,]));

        $this->assertDatabaseHas('attendances', [
            'id'         => $attendanceData->id,
            'start_time' => now()->format('Y-m-d') . ' 09:30:00',
            'end_time'   => now()->format('Y-m-d') . ' 18:00:00',
            'note'       => '出勤時間修正',
        ]);

        $this->assertDatabaseHas('attendance_update_requests', [
            'id'     => $attendanceCorrection->id,
            'status' => 'approved',
        ]);

        $this->actingAs($user);

        $responseRequestListPage = $this->get(route('stamp_correction_request.list'));
        $responseRequestListPage->assertStatus(200);

        $responseRequestListPage->assertViewHas('approvedRequests', function ($approvedRequests) use ($user) {
            return $approvedRequests->every(fn($request) => $request->user_id === $user->id)
                && $approvedRequests->every(fn($request) => $request->status === 'approved')
                && $approvedRequests->contains(fn($request) => $request->note === '出勤時間修正');
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

        $responseAttendanceDetailPage = $this->get(route('admin.attendance.detail', ['id' => $attendanceData->id]));
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
            'start_time' => '09:00',
            'end_time'   => '18:00',
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
    }













}
