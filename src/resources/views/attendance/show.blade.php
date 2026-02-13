@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
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
                        @if ($isEditable)
                        <div class="form__input-time">
                            <input type="text" class="start-time"
                                name="new_start_time" value="{{ old('new_start_time') ?? optional($displayStartTime)->format('H:i') }}">
                            <span>～</span>
                            <input type="text" class="end-time"
                                name="new_end_time" value="{{ old('new_end_time') ?? optional($displayEndTime)->format('H:i') }}">
                        </div>
                        @error('new_start_time')
                        <div class="form__error">
                            {{ $message }}
                        </div>
                        @enderror
                        @else
                        <div class="time-content">
                            <p class="time-content-display">{{ optional($displayStartTime)->format('H:i') }}
                                <span>～</span>
                                {{ optional($displayEndTime)->format('H:i') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @foreach ($displayBreaks as $index => $break)
                <div class="form__group form__group--break">
                    <div class="form__group-title">
                        <p class="form__label-break">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</p>
                    </div>
                    <div class="form__group-content">
                        @if ($isEditable)
                        <div class="form__input-break">
                            <input type="text" class="break-start" name="breaks[{{ $index }}][new_break_start_time]" value="{{ old("breaks.$index.new_break_start_time") ?? optional($break->break_start_time ?? $break->new_break_start_time)->format('H:i') }}">
                            <span>～</span>
                            <input type="text" class="break-end" name="breaks[{{ $index }}][new_break_end_time]" value="{{ old("breaks.$index.new_break_end_time") ?? optional($break->break_end_time ?? $break->new_break_end_time)->format('H:i') }}">
                        </div>

                        @error("breaks.$index.new_break_start_time")
                        <div class="form__error">
                            {{ $message }}
                        </div>
                        @enderror
                        @else
                        <div class="time-content">
                            <p class="time-content-display">
                                {{ optional($break->new_break_start_time)->format('H:i') }}
                                <span>～</span>
                                {{ optional($break->new_break_end_time)->format('H:i') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
                @if ($isEditable)
                <div class="form__group form__group--break">
                    <div class="form__group-title">
                        <p class="form__label-break">{{ $displayBreaks->count() === 0 ? '休憩' : '休憩' . ($displayBreaks->count() + 1) }}</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-break">
                            <input type="text" inputmode="numeric"
                                pattern="[0-9:]*" class="break-start" name="breaks[{{ $displayBreaks->count() }}][new_break_start_time]" value="{{ old("breaks.{$displayBreaks->count()}.new_break_start_time") }}">
                            <span>～</span>
                            <input type="text" inputmode="numeric"
                                pattern="[0-9:]*" class="break-end" name="breaks[{{ $displayBreaks->count() }}][new_break_end_time]" value="{{ old("breaks.{$displayBreaks->count()}.new_break_end_time") }}">
                        </div>
                        @error("breaks." . $displayBreaks->count() . ".new_break_start_time")
                        <div class="form__error">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
                @endif
                <div class="form__group form__group--comment {{ $isPending ? 'is-pending' : '' }}">
                    <div class="form__group-title">
                        <p class="form__label-comment">備考</p>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input-comment">
                            <textarea class="comment" name="comment" id="comment" {{ $isPending ? 'disabled' : '' }}>{{ old('comment', optional($application)->comment) }}</textarea>
                        </div>
                        @error('comment')
                        <div class="form__error">
                            {{ $message }}
                        </div>
                        @enderror
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