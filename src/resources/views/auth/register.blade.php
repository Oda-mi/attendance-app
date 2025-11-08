@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')

<div class="auth">
    <div class="auth__content">
        <h1 class="auth__heading">会員登録</h1>
        <form action="{{ route('register') }}" method="post" class="auth__form" novalidate>
        @csrf
        <div class="auth__form-group">
            <label for="name" class="auth__form-label">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="auth__form-input">
            <div class="auth__form-error">
                @error('name')
                {{ $message }}
                @enderror
            </div>
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
        <div class="auth__form-group">
            <label for="password" class="auth__form-label">パスワード確認</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="auth__form-input">
            <div class="auth__form-error">
                @error('password')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="auth__form-button">
            <button class="auth__button-submit" type="submit">登録する</button>
        </div>
        <div class="auth__link">
            <a href="{{ route('login') }}" class="auth__link-button">ログインはこちら</a>
        </div>
        </form>
    </div>
</div>

@endsection