@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/admin_staff_list.css') }}">
@endsection


@section('content')

<div class="common-table">
    <div class="common-table__title">
        <h1>
            <span class="common-table__title--line"></span>
        スタッフ一覧
        </h1>
    </div>
    <div class="common-table__table">
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="staff-name">テスト太郎</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>テスト太郎２</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                    <td>テスト太郎３</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>テスト太郎４</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>テスト太郎５</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>テスト太郎６</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>テスト太郎７</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>テスト太郎８</td>
                    <td>testtaro@email.com</td>
                    <td><a href="/attendance/detail/{id}" class="common-table__detail-btn">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection