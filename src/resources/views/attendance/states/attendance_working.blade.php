@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/states/attendance_base.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance/states/attendance_working.css') }}">

@section('content')

<div class="attendance">
    <div class="attendance__status">出勤中</div>
    <div class="attendance__date">
        {{now()->locale('ja')->translatedFormat('Y年m月d日(D)') }}
    </div>
    <div class="attendance__time">
        {{ now()->format('H:i') }}
    </div>

    <form action="" method="post" class="attendance__buttons">
        @csrf
        <button type="submit" class="attendance__button attendance__button--clockout">退勤</button>
        <button type="submit" class="attendance__button attendance__button--breakin">休憩入</button>
    </form>
</div>

@endsection