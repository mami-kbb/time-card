@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/requests/index.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="request-list__content">
    <div class="content-header">
        <h2 class="content-title"><span></span>申請一覧</h2>
    </div>
    <div class="tabs">
        <a href="/stamp_correction_request/list?tab=pending" class="{{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="/stamp_correction_request/list?tab=approved" class="{{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>
    <table class="application-list__table">
        <thead>
            <tr class="application-list__table--row">
                <th class="application-list__table--header-condition">　状態</th>
                <th class="application-list__table--header-name">名前</th>
                <th class="application-list__table--header-date">対象日時</th>
                <th class="application-list__table--header-reason">申請理由</th>
                <th class="application-list__table--header-application-date">申請日時</th>
                <th class="application-list__table--header-detail">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($applications as $application)
            <tr class="application-list__table--row">
                <td class="application-list__table--content-condition">{{ $application->status_label }}</td>
                <td class="application-list__table--content-name">{{ $application->user->name }}</td>
                <td class="application-list__table--content-date">{{ $application->attendance->work_date->format('Y/m/d') }}</td>
                <td class="application-list__table--content-reason">{{ $application->comment }}</td>
                <td class="application-list__table--content-application-date">{{ $application->created_at->format('Y/m/d') }}</td>
                <td class="application-list__table--content-detail">
                    <a href="/attendance/detail/{{ $application->id }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection