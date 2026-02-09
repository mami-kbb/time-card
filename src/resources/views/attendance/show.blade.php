@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="show-content">
    <h2 class="content-header"><span></span>勤怠詳細</h2>
    <div class="detail">
        <form action="/attendance/detail/{{ $day['date']->format('Y-m-d') }}" class="application-form" method="post" onsubmit="{{ $isPending ? 'return false;' : '' }}">
            @csrf
            <div class="form-area">
                <div class="form__group">
                    <div class="form__group-title">
                        <span class="form__label-name">名前</span>
                    </div>
                    <div class="form__group-content">
                        <span class="form__group-content-name">{{ $user['name'] }}</span>
                    </div>
                </div>
                <div class="form__group">
                    <div class="form__group-title">
                        <span class="form__label-date">日付</span>
                    </div>
                    <div class="form__group-content">
                        <span class="year">{{ $day['date']->translatedFormat('Y年') }}</span>
                        <span class="date">{{ $day['date']->translatedFormat('n月j日') }}</span>
                    </div>
                </div>
                <div class="form__group form__group--time">
                    <div class="form__group-title">
                        <p class="form__label-time">出勤・退勤</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-time">
                            <input type="time" class="start-time" name="start_time" value="{{ old('start_time') ?? ($attendance?->start_time ? substr($attendance->start_time, 0, 5) : '') }}" {{ $isPending ? 'disabled' : '' }}>
                            <span>～</span>
                            <input type="time" class="end-time" name="end_time" value="{{ old('end_time', optional($attendance)->end_time) }}" {{ $isPending ? 'disabled' : '' }}>
                        </div>
                    </div>
                </div>
                <div class="form__group form__group--break">
                    <div class="form__group-title">
                        <p class="form__label-break1">休憩</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-break1">
                            <input type="time" class="break-start" name="break1_start" value="{{ old('break1_start', optional($break1)->break_start_time) }}" {{ $isPending ? 'disabled' : '' }}>
                            <span>～</span>
                            <input type="time" class="break-end" name="break1_end" value="{{ old('break1_end', optional($break1)->break_end_time) }}" {{ $isPending ? 'disabled' : '' }}>
                        </div>
                    </div>
                </div>
                <div class="form__group form__group--break">
                    <div class="form__group-title">
                        <p class="form__label-break2">休憩２</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-break2">
                            <input type="time" class="break-start" name="break2_start" value="{{ old('break2_start', optional($break2)->break_start_time) }}" {{ $isPending ? 'disabled' : '' }}>
                            <span>～</span>
                            <input type="time" class="break-end" name="break2_end" value="{{ old('break2_end', optional($break2)->break_end_time) }}" {{ $isPending ? 'disabled' : '' }}>
                        </div>
                    </div>
                </div>
                <div class="form__group form__group--comment">
                    <div class="form__group-title">
                        <p class="form__label-comment">備考</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-comment">
                            <textarea class="comment" name="comment" id="comment" {{ $isPending ? 'disabled' : '' }}>{{ old('comment', optional($application)->comment) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-btn">
                @if ($isPending)
                <p class="pending-message">*承認待ちのため修正はできません。</p>
                @else
                <button class="form-btn__submit" type="submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection