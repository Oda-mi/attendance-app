@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')

<div class="attendance">
    <div class="attendance__title">
        <h1>
            <span class="attendance__title--line"></span>
        勤怠一覧
        </h1>
    </div>
    <div class="attendance__nav">
        <button>
            <svg class="arrow-icon arrow-icon-left" xmlns="http://www.w3.org/2000/svg"
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
        </button>
        <span>
            <img src="/images/calendar.svg" class="calendar-icon">
            2025/11
        </span>
        <button>
            翌月
            <svg class="arrow-icon arrow-icon-right" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 161 120" preserveAspectRatio="xMidYMid meet">
                <g transform="translate(0,120) scale(0.1,-0.1)" stroke="none">
                <path d="M585 1186 c-16 -7 -149 -125 -295 -262 -287 -271 -294 -280 -274
                -361 8 -31 52 -78 277 -290 177 -165 281 -256 303 -263 92 -31 192 79 154 169
                -6 16 -74 88 -150 160 l-139 130 534 3 c523 3 534 3 562 24 41 30 59 92 41
                146 -7 23 -26 50 -41 62 -28 21 -39 21 -562 24 l-534 3 139 130 c76 72 144
                144 150 160 25 59 -12 138 -76 165 -40 17 -51 17 -89 0z"/>
                </g>
            </svg>
        </button>
    </div>
    <div class="attendance__table">
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
                <tr>
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>06/02(金)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>                </tr>
                <tr>
                    <td>06/03(土)</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>06/04(日)</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>06/05(月)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>06/06(火)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>06/07(水)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>06/08(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="/attendance/detail/{id}" class="attendance__detail-btn">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection