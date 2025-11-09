@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/request_approve.css') }}">
@endsection

@section('content')

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
                            <div class="user-name">テスト 太郎</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td colspan="2">
                        <div class="attendance-detail__date">
                            <div class="date-year">2025年</div>
                            <div>11月01日</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td colspan="3">
                        <div class="attendance-detail__time">
                            <div class="start_time">09:00</div>
                            <span>～</span>
                            <div>18:00</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td colspan="3">
                        <div class="attendance-detail__time">
                            <div class="start_time">12:00</div>
                            <span>～</span>
                            <div>13:00</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩２</th>
                    <td colspan="3">
                        <div class="attendance-detail__time">
                            <div class="start_time"></div>
                            <span></span>
                            <div></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        <div class="attendance-detail__comment">
                            電車遅延のため
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="attendance-detail__button">
            <button type="submit">承認</button>
        </div>
    </form>
</div>

@endsection