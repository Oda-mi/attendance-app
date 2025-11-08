@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/states/attendance_base.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance/states/before_work.css') }}">

@section('content')

<div class="attendance">
    <div class="attendance__status">勤務外</div>
    <div class="attendance__date">
        {{now()->locale('ja')->translatedFormat('Y年m月d日(D)') }}
    </div>
    <div class="attendance__time">
        {{ now()->format('H:i') }}
    </div>

    <form action="" method="post">
        @csrf
        <button type="submit" class="attendance__button">出勤</button>
    </form>
</div>

@endsection