@extends('users.layout')

@section('title', 'تعديل صلاحيات المنتسب')
@section('page-title', 'تعديل صلاحيات المنتسب')

@section('header-actions')
    <a class="btn" href="{{ route('permissions.members.index') }}">رجوع لقائمة الصلاحيات</a>
@endsection

@section('topbar-actions')
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn warn">تسجيل الخروج</button>
    </form>
@endsection

@section('content')
    @php
        $roleLabels = collect(config('permissions.role_templates', []))
            ->mapWithKeys(fn ($config, $name) => [$name => $config['display_name'] ?? $name])
            ->all();

        $permissionLabels = [];
        foreach (config('permissions.modules', []) as $moduleConfig) {
            foreach (($moduleConfig['permissions'] ?? []) as $permissionName => $displayName) {
                $permissionLabels[$permissionName] = $displayName;
            }
        }
    @endphp

    <p class="muted" style="margin-top: 0;">المستخدم: <strong>{{ $user->username }}</strong></p>

    <form method="POST" action="{{ route('permissions.members.update', $user) }}" class="form-grid">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="legacy_role">الدور التشغيلي (legacy)</label>
            <select id="legacy_role" name="legacy_role">
                <option value="admin" @selected(old('legacy_role', $user->role) === 'admin')>{{ $roleLabels['admin'] ?? 'admin' }}</option>
                <option value="asker" @selected(old('legacy_role', $user->role) === 'asker')>{{ $roleLabels['asker'] ?? 'asker' }}</option>
                <option value="responder" @selected(old('legacy_role', $user->role) === 'responder')>{{ $roleLabels['responder'] ?? 'responder' }}</option>
                <option value="reviewer" @selected(old('legacy_role', $user->role) === 'reviewer')>{{ $roleLabels['reviewer'] ?? 'reviewer' }}</option>
            </select>
        </div>

        <div class="field full">
            <label>الأدوار</label>
            <div class="actions">
                @php($currentRoles = old('roles', $user->roles->pluck('name')->toArray()))
                @foreach ($roles as $role)
                    <label class="btn" style="cursor:pointer;">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, $currentRoles, true))>
                        {{ $roleLabels[$role->name] ?? $role->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="field full">
            <label>اختصاصات المجيب</label>
            @php($scopeLabels = \App\Models\AppUser::RESPONDER_SCOPE_LABELS)
            @php($currentScopes = old('responder_scopes', $user->normalizedResponderScopes()))
            <div class="actions">
                @foreach ($scopeLabels as $scopeValue => $scopeLabel)
                    <label class="btn" style="cursor:pointer;">
                        <input type="checkbox" name="responder_scopes[]" value="{{ $scopeValue }}" @checked(in_array($scopeValue, $currentScopes, true))>
                        {{ $scopeLabel }}
                    </label>
                @endforeach
            </div>
            <span class="muted">عند اختيار "كل الأنواع" سيظهر للمجيب جميع الاستفسارات.</span>
        </div>

        <div class="field full">
            <label>الصلاحيات المباشرة</label>
            <div class="actions">
                @php($currentPermissions = old('permissions', $user->permissions->pluck('name')->toArray()))
                @foreach ($permissions as $permission)
                    <label class="btn" style="cursor:pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, $currentPermissions, true))>
                        {{ $permissionLabels[$permission->name] ?? $permission->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">حفظ الصلاحيات</button>
            </div>
        </div>
    </form>
@endsection
