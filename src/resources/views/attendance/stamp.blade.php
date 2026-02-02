@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/stamp.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="stamp-content">
    <div class="status-content">
        <p class="status">
            @switch($status)
            @case('off')
            勤務外
            @break
            @case('working')
            出勤中
            @break
            @case('break')
            休憩中
            @break
            @case('finished')
            退勤済
            @break
            @endswitch
        </p>
    </div>
    <div class="day-content">
        <p class="date">{{ $now->translatedFormat('Y年n月j日(D)') }}</p>
        <p class="time">{{ $now->translatedFormat('H:i') }}</p>
    </div>
    <div class="buttons">
        @switch($status)
        @case('off')
        <form class="stamp-form
        " method="POST" action="/attendance">
            @csrf
            <input type="hidden" name="action" value="start">
            <div class="form-btn">
                <button class="form-btn__submit" type="submit">出勤</button>
            </div>
        </form>
        @break
        @case('working')
        <div class="btn">
            <form class="stamp-form" method="POST" action="/attendance">
                @csrf
                <input type="hidden" name="action" value="end">
                <div class="form-btn">
                    <button class="form-btn__submit" type="submit">退勤</button>
                </div>
            </form>
            <form class="stamp-form" method="POST" action="/attendance">
                @csrf
                <input type="hidden" name="action" value="break_start">
                <div class="form-btn">
                    <button class="break-form-btn__submit" type="submit">休憩入り</button>
                </div>
            </form>
        </div>

        @break
        @case('break')
        <form class="stamp-form
        " method="POST" action="/attendance">
            @csrf
            <input type="hidden" name="action" value="break_end">
            <div class="form-btn">
                <button class="break-form-btn__submit" type="submit">休憩戻り</button>
            </div>
        </form>
        @break
        @case('finished')
        <p class="message">お疲れ様でした。</p>
        @break
        @endswitch
    </div>
</div>
@endsection