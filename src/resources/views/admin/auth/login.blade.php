@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="login-form__content">
    <div class="login-form__heading">
        <h2>管理者ログイン</h2>
    </div>
    <form action="/admin/login" class="login-form" method="post" novalidate>
        @csrf
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
            <div class="form__error">
                @if ($errors->has('login'))
                <p class="error">{{ $errors->first('login') }}</p>
                @endif
            </div>
        </div>
        <div class="form__btn">
            <button class="form__btn-submit" type="submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection