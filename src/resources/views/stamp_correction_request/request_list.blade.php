@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/request_list.css') }}">
@endsection

@section('content')


<div class="common-table">
    <div class="common-table__title">
        <h1>
            <span class="common-table__title--line"></span>
        申請一覧
        </h1>
    </div>

    <div class="nav">
        <div class="nav__tabs">
            <a href="" class="nav__tab">承認待ち</a>
            <a href="" class="nav__tab">承認済み</a>
        </div>
    </div>

    <div class="common-table__table">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>承認待ち</td>
                    <td>テスト太郎太郎</td>
                    <td>2025/11/01</td>
                    <td>遅延のため</td>
                    <td>2025/11/02</td>
                    <td><a href="/stamp_correction_request/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>テスト太郎</td>
                    <td>2025/11/01</td>
                    <td>遅延のため</td>
                    <td>2025/11/02</td>
                    <td><a href="/stamp_correction_request/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>テスト太郎</td>
                    <td>2025/11/01</td>
                    <td>遅延のため</td>
                    <td>2025/11/02</td>
                    <td><a href="/stamp_correction_request/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>テスト太郎</td>
                    <td>2025/11/01</td>
                    <td>遅延のため</td>
                    <td>2025/11/02</td>
                    <td><a href="/stamp_correction_request/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>テスト太郎</td>
                    <td>2025/11/01</td>
                    <td>遅延のため</td>
                    <td>2025/11/02</td>
                    <td><a href="/stamp_correction_request/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection




