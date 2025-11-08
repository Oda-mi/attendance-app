@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')

<div class="auth">
    <div class="auth__content">
        <h1 class="auth__heading">ログイン</h1>
        <form action="{{ route('login') }}" method="post" class="auth__form" novalidate>
        @csrf
        <div class="auth__form-group">
            <label for="email" class="auth__form-label">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="auth__form-input">
            <div class="auth__form-error">
                @error('email')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="auth__form-group">
            <label for="password" class="auth__form-label">パスワード</label>
            <input type="password" name="password" id="password" class="auth__form-input">
            <div class="auth__form-error">
                @error('password')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="auth__form-button">
            <button class="auth__button-submit" type="submit">ログインする</button>
        </div>
        <div class="auth__link">
            <a href="{{ route('register') }}" class="auth__link-button">会員登録はこちら</a>
        </div>
        </form>
    </div>
</div>

@endsection