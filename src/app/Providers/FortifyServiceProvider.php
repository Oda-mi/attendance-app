<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Illuminate\Support\Facades\Auth;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {

            public function toResponse($request)
            {
                $redirectTo = $request->input('redirect_to', '/login');
                    return redirect($redirectTo);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view ('auth.register');
        });

        Fortify::loginView(function () {

            $path = request()->path();

            if ($path === 'admin/login') {
                return view('admin.auth.admin_login');
            }

            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('verification.notice');
            }
        });


        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません'],
                ]);
            }

            $isAdminLogin = $request->input('is_admin_login', 0);

            if ($isAdminLogin && ! $user->is_admin) {
                throw ValidationException::withMessages([
                    'email' => ['このアカウントではログインできません'],
                ]);
            }

            if (! $isAdminLogin && $user->is_admin) {
                throw ValidationException::withMessages([
                    'email' => ['このアカウントではログインできません'],
                ]);
            }

            return $user;
        });


        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                $user = Auth::user();

                if (! $user->is_admin && !$user->hasVerifiedEmail()) {
                    return redirect()->route('verification.notice');
                }

                if ($user->is_admin) {
                    return redirect()->route('admin.attendance.list');;
                }

                return redirect()->route('attendance.index');
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });


        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

    }
}
