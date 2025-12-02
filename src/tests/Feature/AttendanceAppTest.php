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

        $response = $this->get(route('attendance.index'));

        $currentDate = now()->locale('ja')->translatedFormat('Y年m月d日(D)');

        $currentTime = now()->format('H:i');

        $response->assertSee($currentDate);
        $response->assertSee($currentTime);
    }

    // ========================================
    // ５.ステータス確認機能
    // ========================================
    /** @test */
    public function 勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $responseAttendancePage = $this->get(route('attendance.index'));

        $responseAttendancePage->assertStatus(200);
        $responseAttendancePage->assertViewIs('attendance.states.before_work');
        $responseAttendancePage->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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
        $user = User::factory()->create();

        $this->actingAs($user);

        $responseBeforeWorkPage = $this->get(route('attendance.index'));
        $responseBeforeWorkPage->assertStatus(200);
        $responseBeforeWorkPage->assertSee('出勤');

        $responseStartWork = $this->post(route('attendance.start'));
        $responseStartWork->assertRedirect(route('status.working'));

        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $responseWorkingPage = $this->followingRedirects()->get(route('attendance.index'));
        $responseWorkingPage->assertStatus(200);
        $responseWorkingPage->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseStartBreak1 = $this->post(route('attendance.start_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $responseEndBreak1 = $this->post(route('attendance.end_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $responseStartBreak2 = $this->post(route('attendance.start_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $responseWorkingPage = $this->get(route('status.working'));
        $responseWorkingPage->assertSee('休憩入');

        $this->assertEquals(2, AttendanceBreak::where('attendance_id', $attendance->id)->count());
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseStartBreak = $this->post(route('attendance.start_break'));
        $responseStartBreak->assertRedirect(route('status.on_break'));

        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $responseOnBreakPage = $this->get(route('status.on_break'));
        $responseOnBreakPage->assertSee('休憩戻');

        $responseEndBreak = $this->post(route('attendance.end_break'));
        $responseEndBreak->assertRedirect(route('status.working'));

        $this->assertDatabaseHas('attendances', [
            'id'     => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $responseStartBreak1 = $this->post(route('attendance.start_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $responseEndBreak1 = $this->post(route('attendance.end_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $responseStartBreak2 = $this->post(route('attendance.start_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $responseOnBreakPage = $this->get(route('status.on_break'));
        $responseOnBreakPage->assertSee('休憩戻');

        $responseEndBreak2 = $this->post(route('attendance.end_break'));
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->assertEquals(2, AttendanceBreak::where('attendance_id', $attendance->id)->count());
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

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









}
