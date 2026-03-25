@extends('users.layout')

@section('title', 'تعديل مستخدم')
@section('page-title', 'تعديل المستخدم: ' . $user->username)

@section('header-actions')
    <a class="btn" href="{{ route('users.index') }}">رجوع للقائمة</a>
    <a class="btn" href="{{ route('users.show', $user) }}">عرض البيانات</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('users.update', $user) }}" style="display:grid; gap:12px;">
        @csrf
        @method('PUT')
        @include('users.partials.form', ['user' => $user])
        <div class="actions">
            <button class="btn primary" type="submit">حفظ التعديلات</button>
        </div>
    </form>
@endsection
