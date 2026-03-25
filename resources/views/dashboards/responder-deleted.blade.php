@extends('users.layout')

@section('title', 'المحذوف مؤخرا')
@section('page-title', 'الاستفسارات المحذوفة مؤخرا')

@section('topbar-actions')
    <a class="btn" href="{{ route('user.info') }}">معلومات المستخدم</a>
    <a class="btn" href="{{ route('dashboard.responder') }}">عودة للفهرس</a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المستفسر</th>
                    <th>عنوان الاستفسار</th>
                    <th>تاريخ الحذف</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->id }}</td>
                        <td>{{ $inquiry->asker?->username ?? '-' }}</td>
                        <td>{{ $inquiry->title }}</td>
                        <td>{{ $inquiry->deleted_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('responder.inquiries.restore', $inquiry->id) }}" style="margin:0;">
                                @csrf
                                <button class="btn" type="submit">استعادة</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">لا توجد عناصر محذوفة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($inquiries->hasPages())
        <div class="pager">
            @if ($inquiries->onFirstPage())
                <span class="btn" aria-disabled="true">السابق</span>
            @else
                <a class="btn" href="{{ $inquiries->previousPageUrl() }}">السابق</a>
            @endif

            <span class="btn">صفحة {{ $inquiries->currentPage() }} من {{ $inquiries->lastPage() }}</span>

            @if ($inquiries->hasMorePages())
                <a class="btn" href="{{ $inquiries->nextPageUrl() }}">التالي</a>
            @else
                <span class="btn" aria-disabled="true">التالي</span>
            @endif
        </div>
    @endif
@endsection
