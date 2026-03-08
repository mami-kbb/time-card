@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="list-content">
    <div class="content-header">
        <h2 class="content-title">{{ $user->name }}さんの勤怠</h2>
    </div>
    <div class="month-list">
        <div class="month-header">
            <a href="/admin/attendance/staff/{{ $user->id }}?month={{ $currentMonth->copy()->subMonth()->format('Y-m') }}" class="previous-month"><img class="left-arrow__img" src="{{ asset('/images/arrow.png') }}" alt="←">前月</a>
            <form action="/admin/attendance/staff/{{ $user->id }}" method="get" class="month-select-form">
                <div class="month" id="monthPicker">
                    <img class="calender__img" src="{{ asset('/images/calender.png') }}" alt="calender">
                    <span class="month-text">
                        {{ $currentMonth->format('Y/m') }}
                    </span>
                    <input type="month" name="month" class="month-select-form-input" value="{{ $currentMonth->format('Y-m') }}" id="monthInput" onchange="this.form.submit()">
                </div>
            </form>
            <a href="/admin/attendance/staff/{{ $user->id }}?month={{ $currentMonth->copy()->addMonth()->format('Y-m') }}" class="next-month">翌月<img class="right-arrow__img" src="{{ asset('/images/arrow.png') }}" alt="←"></a>
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
                        {{ $day->attendance->start_time_formatted }}
                        @endif
                    </td>
                    <td class="attendance-logs__table--content-end">@if($day->attendance && $day->attendance->end_time)
                        {{ $day->attendance->end_time_formatted }}
                        @endif
                    </td>
                    <td class="attendance-logs__table--content-break">
                        {{ $day->attendance?->formattedBreakTime() }}
                    </td>
                    <td class="attendance-logs__table--content-total">
                        {{ $day->attendance?->formattedWorkTime() }}
                    </td>
                    <td class="attendance-logs__table--content">
                        <a href="/admin/attendance/{{ $user->id }}/{{ $day->date->format('Y-m-d') }}" class="attendance-logs__table-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="csv-content">
        <a href="/admin/attendance/staff/{{ $user->id }}/csv?month={{ $currentMonth->format('Y-m') }}"
            class="csv-button">
            CSV出力
        </a>
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