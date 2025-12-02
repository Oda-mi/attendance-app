@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/layouts/common_table.css') }}">
@endsection

@section('content')

@php
use Carbon\Carbon;
@endphp

<div class="common-table">
    <div class="common-table__title">
        <h1>
            <span class="common-table__title--line"></span>
        勤怠一覧
        </h1>
    </div>
    <div class="common-table__nav">
        <a href="{{ route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">
            <svg class="arrow-icon common-table__arrow-left" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 161 120" preserveAspectRatio="xMidYMid meet">
                <g transform="translate(0,120) scale(0.1,-0.1)" stroke="none">
                <path d="M585 1186 c-16 -7 -149 -125 -295 -262 -287 -271 -294 -280 -274
                -361 8 -31 52 -78 277 -290 177 -165 281 -256 303 -263 92 -31 192 79 154 169
                -6 16 -74 88 -150 160 l-139 130 534 3 c523 3 534 3 562 24 41 30 59 92 41
                146 -7 23 -26 50 -41 62 -28 21 -39 21 -562 24 l-534 3 139 130 c76 72 144
                144 150 160 25 59 -12 138 -76 165 -40 17 -51 17 -89 0z"/>
                </g>
            </svg>
            前月
        </a>
        <span>
            <img src="/images/calendar.svg" class="calendar-icon">
            {{ $displayMonth }}
        </span>
        <a href="{{ route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">
            翌月
            <svg class="arrow-icon common-table__arrow-right" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 161 120" preserveAspectRatio="xMidYMid meet">
                <g transform="translate(0,120) scale(0.1,-0.1)" stroke="none">
                <path d="M585 1186 c-16 -7 -149 -125 -295 -262 -287 -271 -294 -280 -274
                -361 8 -31 52 -78 277 -290 177 -165 281 -256 303 -263 92 -31 192 79 154 169
                -6 16 -74 88 -150 160 l-139 130 534 3 c523 3 534 3 562 24 41 30 59 92 41
                146 -7 23 -26 50 -41 62 -28 21 -39 21 -562 24 l-534 3 139 130 c76 72 144
                144 150 160 25 59 -12 138 -76 165 -40 17 -51 17 -89 0z"/>
                </g>
            </svg>
        </a>
    </div>
    <div class="common-table__table">
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceDays as $attendance)
                <tr>
                    <td>{{ Carbon::parse($attendance->work_date)->locale('ja')->translatedFormat('m/d(D)') }}</td>
                    <td>{{ $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    <td>
                        @if($attendance->start_time && $attendance->end_time)
                            {{ gmdate('H:i', $attendance->breakTotal ?? 0) }}
                        @else
                            {{-- 出勤・退勤の両方がない場合、または退勤していない場合は空白 --}}
                        @endif
                    </td>
                    <td>{{ $attendance->workTotal ? gmdate('H:i', $attendance->workTotal) : '' }}</td>
                    <td><a href="{{ route('attendance.detail', ['id' => $attendance->id, 'date' => $attendance->work_date]) }}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection