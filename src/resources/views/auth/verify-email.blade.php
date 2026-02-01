@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email">
    <p class="verify-email__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    <a href="http://localhost:8025" target="_blank">認証はこちらから</a>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">認証メールを再送する</button>
    </form>

</div>

@endsection