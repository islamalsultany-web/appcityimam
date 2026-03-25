@extends('users.layout')

@section('title', 'إنشاء مستخدم')
@section('page-title', 'إنشاء مستخدم جديد')

@section('header-actions')
    <a class="btn" href="{{ route('users.index') }}">رجوع للقائمة</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('users.store') }}" style="display:grid; gap:12px;">
        @csrf
        @include('users.partials.form', ['user' => null])
        <div class="actions">
            <button class="btn primary" type="submit">حفظ المستخدم</button>
        </div>
    </form>
@endsection
