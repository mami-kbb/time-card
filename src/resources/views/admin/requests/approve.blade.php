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
        <h2 class="content-title"><span></span>勤怠詳細</h2>
    </div>
    <div class="detail">
        <form action="/stamp_correction_request/approve/{{ $application->id }}" class="application-form" method="post" onsubmit="{{ $isPending ? 'return false;' : '' }}">
            @csrf
            <div class="form-area">
                <div class="form__group">
                    <div class="form__group-title">
                        <p class="form__label-name">名前</p>
                    </div>
                    <div class="form__group-content">
                        <p class="form__group-content-name">{{ $attendance->user->name }}</p>
                        <input type="hidden" class="name" name="name" value="{{ $attendance->user->name }}">
                    </div>
                </div>
                <div class="form__group">
                    <div class="form__group-title">
                        <p class="form__label-date">日付</p>
                    </div>
                    <div class="form__group-content">
                        <span class="year">{{ $workDate->translatedFormat('Y年') }}</span>
                        <span class="date">{{ $workDate->translatedFormat('n月j日') }}</span>
                    </div>
                </div>
                <div class="form__group form__group--time">
                    <div class="form__group-title">
                        <p class="form__label-time">出勤・退勤</p>
                    </div>
                    <div class="form__group-content">
                        <div class="time-content">
                            <p class="time-content-display">{{ $application->new_start_time?->format('H:i') }}
                                <span>～</span>
                                {{ $application->new_end_time?->format('H:i') }}
                            </p>
                            <input type="hidden" class="start-time"
                                name="new_start_time" value="{{ optional($application)->new_start_time->format('H:i') }}">
                            <input type="hidden" class="end-time"
                                name="new_end_time" value="{{  optional($application)->new_end_time->format('H:i') }}">
                        </div>
                    </div>
                </div>
                @foreach ($displayBreaks as $index => $break)
                <div class="form__group form__group--break">
                    <div class="form__group-title">
                        <p class="form__label-break">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</p>
                    </div>
                    <div class="form__group-content">
                        <div class="time-content">
                            <p class="time-content-display">
                                {{ optional($break->new_break_start_time)->format('H:i') }}
                                <span>～</span>
                                {{ optional($break->new_break_end_time)->format('H:i') }}
                            </p>
                            <input type="hidden" class="break-start" name="breaks[{{ $index }}][new_break_start_time]" value="{{ optional($break->new_break_start_time)->format('H:i') }}">
                            <input type="hidden" class="break-end" name="breaks[{{ $index }}][new_break_end_time]" value="{{ optional($break->new_break_end_time)->format('H:i') }}">
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="form__group form__group--comment">
                    <div class="form__group-title">
                        <p class="form__label-comment">備考</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-comment">
                            <textarea class="comment" name="comment" id="comment" readonly>{{ $application->comment }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-btn">
                @if ($isPending)
                <button class="form-btn__submit" type="submit">承認</button>
                @else
                <p class="pending-message">承認済み</p>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection