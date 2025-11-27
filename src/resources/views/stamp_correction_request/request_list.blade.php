@extends($layout)

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/request_list.css') }}">
@endsection

@section('content')

@php
use Carbon\Carbon;
@endphp

<div class="common-table">
    <div class="common-table__title">
        <h1>
            <span class="common-table__title--line"></span>
        申請一覧
        </h1>
    </div>

    <div class="nav">
        <div class="nav__tabs">
            <a href="#" class="nav__tab nav__tab--active" data-target="pending">承認待ち</a>
            <a href="#" class="nav__tab" data-target="approved">承認済み</a>
        </div>
    </div>

    <div id="pending" class="common-table__table tab-content">
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
                @foreach($pendingRequests ?? [] as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ Carbon::parse($request->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('stamp_correction_request.approve', ['attendance_correct_request_id' => $request->id]) }}" class="common-table__detail-btn">詳細</a>
                        @else
                            <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="common-table__detail-btn">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="approved" class="common-table__table tab-content" style="display:none;">
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
                @foreach($approvedRequests ?? [] as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ Carbon::parse($request->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('stamp_correction_request.approve', ['attendance_correct_request_id' => $request->id]) }}" class="common-table__detail-btn">詳細</a>
                        @else
                            <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="common-table__detail-btn">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.nav__tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(tabButton => {
            tabButton.addEventListener('click', (clickEvent) => {
                clickEvent.preventDefault();

                const targetContentId = tabButton.dataset.target;

                // タブのアクティブ切り替え
                tabButtons.forEach(button => button.classList.remove('nav__tab--active'));
                tabButton.classList.add('nav__tab--active');

                // コンテンツの表示切り替え
                tabContents.forEach(content => {
                    content.style.display = (content.id === targetContentId) ? 'block' : 'none';
                });
            });
        });
    });

</script>

@endpush


