@extends('users.layout')

@section('title', 'عرض مستخدم')
@section('page-title', 'عرض بيانات المستخدم')

@section('header-actions')
    <a class="btn" href="{{ route('users.index') }}">رجوع للقائمة</a>
    <a class="btn primary" href="{{ route('users.edit', $user) }}">تعديل</a>
@endsection

@section('content')
    @php($scopeLabels = \App\Models\AppUser::RESPONDER_SCOPE_LABELS)

    <ul class="list-grid">
        <li><strong>اسم المستخدم:</strong> {{ $user->username }}</li>
        <li><strong>الرقم الوظيفي:</strong> {{ $user->employee_number ?: '-' }}</li>
        <li><strong>رقم الباج:</strong> {{ $user->badge_number ?: '-' }}</li>
        <li><strong>الشعبة:</strong> {{ $user->division ?: '-' }}</li>
        <li><strong>الوحدة:</strong> {{ $user->unit ?: '-' }}</li>
        <li><strong>الدور:</strong> {{ \App\Models\AppUser::ROLE_LABELS[$user->role] ?? $user->role }}</li>
        <li><strong>اختصاصات المجيب:</strong> {{ in_array($user->role, ['responder', 'admin'], true) ? collect($user->normalizedResponderScopes())->map(fn ($scope) => $scopeLabels[$scope] ?? $scope)->join('، ') : '-' }}</li>
    </ul>
@endsection
