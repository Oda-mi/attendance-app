@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/attendance_detail.css') }}">
@endsection

@section('content')

@php
use Carbon\Carbon;
@endphp

<div class="attendance-detail">
    <div class="attendance-detail__title">
        <h1>
            <span class="attendance-detail__title--line"></span>
        勤怠詳細
        </h1>
    </div>

    <form action="" method="post">
        @csrf
        <div class="attendance-detail__table">
            <table>
                <tr>
                    <th>名前</th>
                    <td colspan="3">
                        <div class="attendance-detail__user">
                            <div class="user-name">{{ $user->name }}</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td colspan="2">
                        <div class="attendance-detail__date">
                            <div class="date-year">{{ $attendance->work_date ? Carbon::parse($attendance->work_date)->format('Y年') : '' }}</div>
                            <div>{{ $attendance->work_date ? Carbon::parse($attendance->work_date)->format('m月d日') : '' }}</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            <input type="text" name="start_time" value="{{ $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '' }}">
                            <span>～</span>
                            <input type="text" name="end_time" value="{{ $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '' }}">
                        </div>
                    </td>
                </tr>
                @foreach($breaks as $index => $break)
                <tr>
                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            <input type="text" name="break_start[{{ $index }}]" value="{{ $break->start_time ? Carbon::parse($break->start_time)->format('H:i') : '' }}">
                            <span>～</span>
                            <input type="text" name="break_end[{{ $index }}]" value="{{ $break->end_time ? Carbon::parse($break->end_time)->format('H:i') : '' }}">
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>休憩{{ $breaks->count() + 1 }}</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            <input type="text" name="break_start[{{ $breaks->count() }}]" value="">
                            <span>～</span>
                            <input type="text" name="break_end[{{ $breaks->count() }}]" value="">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        <textarea name="comment" id="" class="attendance-detail__textarea"></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <div class="attendance-detail__button">
            <button type="submit">修正</button>
        </div>
    </form>
</div>

@endsection