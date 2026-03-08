@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/requests/approve.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="show-content">
    <div class="content-header">
        <h2 class="content-title">勤怠詳細</h2>
    </div>
    <div class="detail">
        <div class="detail-area">
            <div class="detail__group">
                <div class="detail__group-title">
                    <p class="detail__label">名前</p>
                </div>
                <div class="detail__group-content">
                    <p class="detail__group-content-name">{{ $attendance->user->name }}</p>
                </div>
            </div>
            <div class="detail__group">
                <div class="detail__group-title">
                    <p class="detail__label">日付</p>
                </div>
                <div class="detail__group-content-date">
                    <p class="year">{{ $workDate->translatedFormat('Y年') }}</p>
                    <p class="date">{{ $workDate->translatedFormat('n月j日') }}</p>
                </div>
            </div>
            <div class="detail__group detail__group--time">
                <div class="detail__group-title">
                    <p class="detail__label">出勤・退勤</p>
                </div>
                <div class="detail__group-content">
                    <div class="time-content">
                        <p class="time-content-display">{{ $application->new_start_time?->format('H:i') }}
                            <span>～</span>
                            {{ $application->new_end_time?->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>
            @foreach ($displayBreaks as $index => $break)
            <div class="detail__group detail__group--break">
                <div class="detail__group-title">
                    <p class="detail__label">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</p>
                </div>
                <div class="detail__group-content">
                    <div class="time-content">
                        <p class="time-content-display">
                            {{ optional($break->new_break_start_time)?->format('H:i') }}
                            <span>～</span>
                            {{ optional($break->new_break_end_time)?->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="detail__group detail__group--comment">
                <div class="detail__group-title">
                    <p class="detail__label">備考</p>
                </div>
                <div class="detail__group-content">
                    <div class="detail__comment">
                        <p class="comment">{{ $application->comment }}</p>
                    </div>
                </div>
            </div>
        </div>
        @if ($isPending)
        <form action="/stamp_correction_request/approve/{{ $application->id }}" class="application-form" method="post">
            @csrf
            <div class="form-btn">
                <button class="form-btn__submit" type="submit">承認</button>
            </div>
        </form>
        @else
        <div class="form-btn">
            <p class="pending-message">承認済み</p>
        </div>
        @endif
    </div>
</div>
@endsection