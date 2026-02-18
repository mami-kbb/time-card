@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="list-content">
    <div class="content-header">
        <h2 class="content-title"><span></span>{{ $currentDate->format('Y年n月j日') }}の勤怠</h2>
    </div>
    <div class="attendance-list">
        <div class="date-header">
            <a href="/admin/attendance/list?date={{ $currentDate->copy()->subDay()->format('Y-m-d') }}" class="previous-date"><img class="left-arrow__img" src="{{ asset('/images/arrow.png') }}" alt="←">前日</a>
            <form action="/admin/attendance/list" method="get" class="date-select-form">
                <div class="date" id="datePicker">
                    <img class="calender__img" src="{{ asset('/images/calender.png') }}" alt="calender">
                    <span class="date-text">
                        {{ $currentDate->format('Y/m/d') }}
                    </span>
                    <input type="date" name="date" class="date-select-form-input" value="{{ $currentDate->format('Y-m-d') }}" id="dateInput" onchange="this.form.submit()">
                </div>
            </form>
            <a href="/admin/attendance/list?date={{ $currentDate->copy()->addDay()->format('Y-m-d') }}" class="next-date">次日<img class="right-arrow__img" src="{{ asset('/images/arrow.png') }}" alt="←"></a>
        </div>
        <table class="attendance-logs__table">
            <thead>
                <tr class="attendance-logs__table--row">
                    <th class="attendance-logs__table--header-name">名前</th>
                    <th class="attendance-logs__table--header-start">出勤</th>
                    <th class="attendance-logs__table--header-end">退勤</th>
                    <th class="attendance-logs__table--header-break">休憩</th>
                    <th class="attendance-logs__table--header-total">合計</th>
                    <th class="attendance-logs__table--header-detail">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                @php
                $attendance = $user->attendances->first();
                @endphp
                <tr class="attendance-logs__table--row">
                    <td class="attendance-logs__table--content-name">{{ $user->name }}</td>
                    <td class="attendance-logs__table--content-start">{{ $attendance?->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                    <td class="attendance-logs__table--content-end">{{ $attendance?->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>
                    <td class="attendance-logs__table--content-break">
                        @if($attendance)
                            @php
                            $breakMinutes = $attendance->calculateTotalBreakTime();
                            $hours = floor($breakMinutes / 60);
                            $minutes = $breakMinutes % 60;
                            @endphp

                            @if ($breakMinutes > 0)
                            {{ $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                            @endif
                        @endif
                    </td>
                    <td class="attendance-logs__table--content-total">
                        @if(!$attendance || !$attendance->start_time)
                        @elseif($attendance->start_time && !$attendance->end_time)
                        ー
                        @else
                        @php
                        $workMinutes = $attendance->calculateTotalWorkTime();
                        $hours = floor($workMinutes / 60);
                        $minutes = $workMinutes % 60;
                        @endphp

                        {{ $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                        @endif
                    </td>
                    <td class="attendance-logs__table--content">
                        @if($attendance)
                        <a href="/attendance/detail/{{ $attendance->id }}" class="attendance-logs__table-detail">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('datePicker').addEventListener('click', () => {
        const input = document.getElementById('dateInput');

        if (input.showPicker) {
            input.showPicker();
        } else {
            input.focus();
        }
    });
</script>

@endsection