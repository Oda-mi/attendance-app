@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/states/status_common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance/states/after_work.css') }}">
@endsection

@section('header-nav')

    <ul class="header__buttons">
        <li><a href="{{ route('attendance.list') }}" class="button button--afterwork">今月の出勤一覧</a></li>
        <li><a href="{{ route('stamp_correction_request.list') }}" class="button button--afterwork">申請一覧</a></li>
        <li>
            <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="button button--logout">ログアウト</button>
            </form>
        </li>
    </ul>

@endsection

@section('content')

<div class="attendance">
    <div class="attendance__status">退勤済</div>
    <div class="attendance__date">
        {{now()->locale('ja')->translatedFormat('Y年m月d日(D)') }}
    </div>
    <div class="attendance__time">
        {{ now()->format('H:i') }}
    </div>
    <div class="attendance__message">
        <p>お疲れ様でした。</p>
    </div>
</div>

@endsection