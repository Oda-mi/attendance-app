@extends($layout)

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

    @if(auth()->user()->is_admin)
        <form action="{{ route('admin.attendance.upsert') }}" method="post">
        @csrf
    @else
        <form action="{{ route('attendance.update_request') }}" method="post">
        @csrf
    @endif
        <input type="hidden" name="attendanceId" value="{{ $attendanceData->id ?? 0 }}">
        <input type="hidden" name="work_date" value="{{ $attendanceData->work_date ?? request('work_date') }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">


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
                            <div class="date-year">{{ $attendanceData->work_date ? Carbon::parse($attendanceData->work_date)->format('Y年') : '' }}</div>
                            <div>{{ $attendanceData->work_date ? Carbon::parse($attendanceData->work_date)->format('m月d日') : '' }}</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            @if($isEditable)
                            <input  type="text"
                                    name="start_time"
                                    value="{{ old('start_time', $attendanceData->start_time ? Carbon::parse($attendanceData->start_time)->format('H:i') : '') }}" >
                            <span>～</span>
                            <input  type="text"
                                    name="end_time"
                                    value="{{ old('end_time',$attendanceData->end_time ? Carbon::parse($attendanceData->end_time)->format('H:i') : '') }}">
                            @else
                            <div class="attendance-detail__pending">
                                {{$attendanceData->start_time ? Carbon::parse($attendanceData->start_time)->format('H:i') : ''}}
                            </div>
                            <span>～</span>
                            <div class="attendance-detail__pending">
                                {{$attendanceData->end_time ? Carbon::parse($attendanceData->end_time)->format('H:i') : ''}}
                            </div>
                            @endif
                        </div>
                        <div class="attendance-form__error">
                        @error('time')
                            {{ $message }}
                        @enderror
                        @error('work_time_format')
                            {{ $message }}
                        @enderror
                        @error('work_time')
                            {{ $message }}
                        @enderror
                        </div>
                    </td>
                </tr>
                @foreach($breaks as $index => $break)
                <tr>
                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            @if($isEditable)
                            <input
                                type="text"
                                name="break_start[{{ $index }}]"
                                value="{{  old('break_start.'.$index,$break->start_time ? Carbon::parse($break->start_time)->format('H:i') : '') }}">
                            <span>～</span>
                            <input
                                type="text"
                                name="break_end[{{ $index }}]"
                                value="{{  old('break_end.'.$index,$break->end_time ? Carbon::parse($break->end_time)->format('H:i') : '') }}">
                            @else
                            <div class="attendance-detail__pending">
                                {{ $break->start_time ? Carbon::parse($break->start_time)->format('H:i') : '' }}
                            </div>
                            <span>～</span>
                            <div class="attendance-detail__pending">
                                {{ $break->end_time ? Carbon::parse($break->end_time)->format('H:i') : '' }}
                            </div>
                            @endif
                        </div>
                        <div class="attendance-form__error">
                            @error("break_start_format.$index")
                                {{ $message }}
                            @enderror
                            @error("break_end_format.$index")
                            {{ $message }}
                            @enderror
                            @error("break_start.$index")
                                {{ $message }}
                            @enderror
                            @error("break_end.$index")
                                {{ $message }}
                            @enderror
                            @error("break_start_end.$index")
                                {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($isEditable)
                <tr>
                    <th>休憩{{ $breaks->count() + 1 }}</th>
                    <td colspan="3">
                        <div class="attendance-detail__time-inputs">
                            <input
                                type="text"
                                name="break_start[{{ $breaks->count() }}]"
                                value="{{ old('break_start.' . $breaks->count()) }}"
                            >
                            <span>～</span>
                            <input
                                type="text"
                                name="break_end[{{ $breaks->count() }}]"
                                value="{{ old('break_end.' . $breaks->count()) }}"
                            >
                        </div>
                        <div class="attendance-form__error">
                            @error("break_start_format." . $breaks->count())
                                {{ $message }}
                            @enderror
                            @error("break_end_format." . $breaks->count())
                                {{ $message }}
                            @enderror
                            @error("break_start." . $breaks->count())
                                {{ $message }}
                            @enderror
                            @error("break_end." . $breaks->count())
                                {{ $message }}
                            @enderror
                            @error("break_start_end." . $breaks->count())
                                {{ $message }}
                            @enderror
                        </div>
                    </td>
                </tr>
                @endif
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        @if($isEditable)
                        <textarea  name="note" class="attendance-detail__textarea">{{ old('note', $attendanceData->note) }}</textarea>
                        @else
                        <div class="attendance-detail__textarea--pending">
                            {!! nl2br(e($attendanceData->note)) !!}
                        </div>
                        @endif
                        <div class="attendance-form__error">
                        @error('note')
                            {{ $message }}
                        @enderror
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        @if($isEditable)
        <div class="attendance-detail__button">
            <button type="submit">修正</button>
        </div>
        @else
        <div class="attendance-detail__message">
            <p class="attendance-detail__message--pending">*承認待ちのため修正はできません。</p>
        </div>
        @endif
    </form>
</div>

@endsection