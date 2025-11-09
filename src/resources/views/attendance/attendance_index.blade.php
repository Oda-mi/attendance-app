@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/attendance_index.css') }}">
@endsection

@section('content')

<div class="attendance">
    @if($status === 'before_work')
        @include('attendance.partials.before_work')
    @elseif($status === 'working')
        @include('attendance.partials.working')
    @elseif($status === 'on_break')
        @include('attendance.partials.on_break')
    @elseif($status === 'after_work')
        @include('attendance.partials.after_work')
    @endif
</div>

@endsection