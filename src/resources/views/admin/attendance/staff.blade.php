@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@endsection

@section('nav')
@include('layouts.header_nav')
@endsection

@section('content')
<div class="list-content">
    <div class="content-header">
        <h2 class="content-title"><span></span>スタッフ一覧</h2>
    </div>
    <div class="staff-list">
        <table class="staff__table">
            <thead>
                <tr class="staff__table--row">
                    <th class="staff__table--header-name">名前</th>
                    <th class="staff__table--header-email">メールアドレス</th>
                    <th class="attendance-logs__table--header-detail">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr class="staff__table--row">
                    <td class="staff__table--content-name">{{ $user->name }}</td>
                    <td class="staff__table--content-email">{{ $user->email }}</td>
                    <td class="staff__table--content">
                        <a href="/admin/attendance/staff/{{ $user->id }}" class="staff__table-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection