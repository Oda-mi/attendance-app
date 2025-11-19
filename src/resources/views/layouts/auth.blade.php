<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/auth.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">    @yield('css')
</head>
<body>
<header class="header">
    <div class="header__inner">
        <div class="header__logo">
            <img src="{{ asset('images/logo.svg') }}" alt="CoachTechLogo">
        </div>
        <div class="header__nav">
            @hasSection('header-nav')
                @yield('header-nav')
            @else
                <ul class="header__buttons">
                    <li><a href="{{ route('attendance.index') }}" class="button">勤怠</a></li>
                    <li><a href="{{ route('attendance.list') }}" class="button">勤怠一覧</a></li>
                    <li><a href="{{ route('stamp_correction_request.list') }}" class="button">申請</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ auth()->user()->is_admin ? '/admin/login' : '/login' }}">
                        <button type="submit" class="button button--logout">ログアウト</button>
                        </form>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</header>

    <main>
        @yield('content')
    </main>

    @stack('scripts')

</body>
</html>