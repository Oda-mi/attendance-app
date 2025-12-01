@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/states/status_common.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance/states/on_break.css') }}">

@section('content')

<div class="attendance">
    <div class="attendance__status">休憩中</div>
    <div class="attendance__date">
        {{now()->locale('ja')->translatedFormat('Y年m月d日(D)') }}
    </div>
    <div class="attendance__time">
        {{ now()->format('H:i') }}
    </div>

    <form action="{{ route('attendance.end_break') }}" method="post">
        @csrf
        <button type="submit" class="attendance__button">休憩戻</button>
    </form>
</div>

@endsection