@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="list-content">
    <div class="content-header">
        <h2 class="content-title"><span></span>勤怠一覧</h2>
    </div>
    <div class="month-list">
        <div class="month-header">
            <a href="/attendance/list?month={{ $currentMonth->copy()->subMonth()->format('Y-m') }}" class="previous-month"><img class="left-arrow__img" src="{{ asset('/images/arrow.png') }}" alt="←">前月</a>
            <form action="/attendance/list" method="get" class="month-select-form">
                <div class="month" id="monthPicker">
                    <img class="calender__img" src="{{ asset('/images/calender.png') }}" alt="calender">
                    <span class="month-text">
                        {{ $currentMonth->format('Y/m') }}
                    </span>
                    <input type="month" name="month" class="month-select-form-input" value="{{ $currentMonth->format('Y-m') }}" id="monthInput" onchange="this.form.submit()">
                </div>
            </form>
            <a href="/attendance/list?month={{ $currentMonth->copy()->addMonth()->format('Y-m') }}" class="next-month">次月<img class="right-arrow__img" src="{{ asset('/images/arrow.png') }}" alt="←"></a>
        </div>
        <table class="attendance-logs__table">
            <thead>
                <tr class="attendance-logs__table--row">
                    <th class="attendance-logs__table--header-date">日付</th>
                    <th class="attendance-logs__table--header-start">出勤</th>
                    <th class="attendance-logs__table--header-end">退勤</th>
                    <th class="attendance-logs__table--header-break">休憩</th>
                    <th class="attendance-logs__table--header-total">合計</th>
                    <th class="attendance-logs__table--header-detail">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dates as $day)
                <tr class="attendance-logs__table--row">
                    <td class="attendance-logs__table--content-day">{{ $day->date->translatedFormat('m/d(D)') }}</td>
                    <td class="attendance-logs__table--content-start">@if($day->attendance && $day->attendance->start_time)
                        {{ \Carbon\Carbon::parse($day->attendance->start_time)->format('H:i') }}
                        @endif
                    </td>
                    <td class="attendance-logs__table--content-end">@if($day->attendance && $day->attendance->end_time)
                        {{ \Carbon\Carbon::parse($day->attendance->end_time)->format('H:i') }}
                        @endif
                    </td>
                    <td class="attendance-logs__table--content-break">
                        @if($day->attendance)
                            @php
                            $breakMinutes = $day->attendance->calculateTotalBreakTime();
                            $hours = floor($breakMinutes / 60);
                            $minutes = $breakMinutes % 60;
                            @endphp

                            @if ($breakMinutes > 0)
                            {{ $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                            @endif
                        @endif
                    </td>
                    <td class="attendance-logs__table--content-total">
                        @if(!$day->attendance)
                        @elseif(!$day->attendance->start_time)
                        @elseif($day->attendance->start_time && !$day->attendance->end_time)
                        ー
                        @else
                        @php
                        $workMinutes = $day->attendance->calculateTotalWorkTime();
                        $hours = floor($workMinutes / 60);
                        $minutes = $workMinutes % 60;
                        @endphp

                        {{ $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    <td class="attendance-logs__table--content">
                        <a href="/attendance/detail/{{ $day->date->format('Y-m-d') }}" class="attendance-logs__table-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('monthPicker').addEventListener('click', () => {
        const input = document.getElementById('monthInput');

        if (input.showPicker) {
            input.showPicker();
        } else {
            input.focus();
        }
    });
</script>

@endsection