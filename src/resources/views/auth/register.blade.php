@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-content">
    <div class="register-form__heading">
        <h2>会員登録</h2>
    </div>
    <form action="/register" class="register-form" method="post" novalidate>
        @csrf
        <div class="form__group">
            <div class="form__group-item">
                <label for="name" class="form__group-label">名前</label>
                <input id="name" type="text" class="form__group-input" name="name" value="{{ old('name') }}">
            </div>
            <div class="form__error">
                @error('name')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__group">
            <div class="form__group-item">
                <label for="email" class="form__group-label">メールアドレス</label>
                <input id="email" type="email" class="form__group-input" name="email" value="{{ old('email') }}">
            </div>
            <div class="form__error">
                @error('email')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__group">
            <div class="form__group-item">
                <label for="password" class="form__group-label">パスワード</label>
                <input id="password" type="password" class="form__group-input" name="password">
            </div>
            <div class="form__error">
                @error('password')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__group">
            <div class="form__group-item">
                <label for="password_confirmation" class="form__group-label">パスワード確認</label>
                <input id="password_confirmation" type="password" class="form__group-input" name="password_confirmation">
            </div>
            <div class="form__error">
                @error('password')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__btn">
            <button class="form__btn-submit" type="submit">登録する</button>
        </div>
    </form>
    <div class="login__link">
        <a class="login__btn-submit" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection