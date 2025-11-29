@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/request_approve.css') }}">
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

    <form action="{{ route('stamp_correction_request.approve', ['attendance_correct_request_id' => $requestData->id]) }}" method="post">
        @csrf
        <div class="attendance-detail__table">
            <table>
                <tr>
                    <th>名前</th>
                    <td colspan="3">
                        <div class="attendance-detail__user">
                            <div class="user-name">{{ $requestData->user->name }}</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td colspan="2">
                        <div class="attendance-detail__date">
                            <div class="date-year">{{ $requestData->work_date ? Carbon::parse($requestData->work_date)->format('Y年') : '' }}</div>
                            <div>{{ $requestData->work_date ? Carbon::parse($requestData->work_date)->format('m月d日') : '' }}</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td colspan="3">
                        <div class="attendance-detail__time">
                            <div class="start_time">
                                {{$requestData->start_time ? Carbon::parse($requestData->start_time)->format('H:i') : ''}}
                            </div>
                            <span>～</span>
                            <div>
                                {{$requestData->end_time ? Carbon::parse($requestData->end_time)->format('H:i') : ''}}
                            </div>
                        </div>
                    </td>
                </tr>
                @foreach($breaks as $index => $break)
                <tr>
                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td colspan="3">
                        <div class="attendance-detail__time">
                            <div class="start_time">
                                {{ $break['start_time'] ? Carbon::parse($break['start_time'])->format('H:i') : '' }}
                            </div>
                            <span>～</span>
                            <div>
                                {{ $break['end_time'] ? Carbon::parse($break['end_time'])->format('H:i') : '' }}
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        <div class="attendance-detail__comment">
                            {!! nl2br(e($requestData->note)) !!}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="attendance-detail__button">
            @if ($requestData->status === 'approved')
                <button class="approved-btn" disabled>承認済み</button>
            @else
                <button type="submit" class="approve-btn">承認</button>
            @endif
        </div>
    </form>
</div>

@endsection