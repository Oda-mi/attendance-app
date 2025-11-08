@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')

<div class="verify-email">
    <div class="verify-email__content">
        <p>
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        <div class="verify-email__form">
            <div class="verify-email__link">
                <a href="http://localhost:8025">認証はこちらから</a>
            </div>
            <form action="{{ route('verification.send') }}" method="post">
                @csrf
                <div class="verify-email__button">
                    <button type="submit">認証メールを再送する</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection