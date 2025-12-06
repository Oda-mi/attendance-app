<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;

use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class AuthTest extends TestCase
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

        $this->assertDatabaseHas('users', [
            'name'  => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
    }


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
        $admin = User::factory()->create([
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
        $admin = User::factory()->create([
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
        $admin = User::factory()->create([
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
    // 16.メール認証機能
    // ========================================
    /** @test */
    public function 会員登録後、認証メールが送信される()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        Notification::assertSentTo(
            User::where('email', 'test@example.com')->first(),
            VerifyEmail::class
        );
    }

    /** @test */
    public function メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');

        $response = $this->post(route('verification.send'));
        $response->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $this->assertNotNull($user->fresh()->email_verified_at);

        $response->assertRedirect(route('attendance.index', ['verified' => 1]));
    }

}
