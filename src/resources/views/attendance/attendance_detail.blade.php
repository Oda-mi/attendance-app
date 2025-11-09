@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/attendance_detail.css') }}">
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
                        <div class="attendance-detail__time-inputs">
                            <input type="text" name="start_time" value="09:00">
                            <span>～</span>
                            <input type="text" name="end_time" value="18:00">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            <input type="text" name="start_time" value="09:00">
                            <span>～</span>
                            <input type="text" name="end_time" value="18:00">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩２</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            <input type="text" name="start_time" value="">
                            <span>～</span>
                            <input type="text" name="end_time" value="">
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