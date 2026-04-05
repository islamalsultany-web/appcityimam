@extends('users.layout')

@section('title', 'إضافة صلاحيات المنتسب')
@section('page-title', 'إضافة صلاحيات المنتسب')

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

    <form method="POST" action="{{ route('permissions.members.store') }}" class="form-grid">
        @csrf

        <div class="field full">
            <label for="user_id">اختر المستخدم</label>
            <select id="user_id" name="user_id" required>
                <option value="">-- اختر منتسب --</option>
                @foreach ($users as $targetUser)
                    <option value="{{ $targetUser->id }}" @selected((string) old('user_id') === (string) $targetUser->id)>
                        {{ $targetUser->username }} (ID: {{ $targetUser->id }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="legacy_role">الدور التشغيلي (legacy)</label>
            <select id="legacy_role" name="legacy_role">
                <option value="">-- بدون تغيير --</option>
                <option value="admin" @selected(old('legacy_role') === 'admin')>{{ $roleLabels['admin'] ?? 'admin' }}</option>
                <option value="asker" @selected(old('legacy_role') === 'asker')>{{ $roleLabels['asker'] ?? 'asker' }}</option>
                <option value="responder" @selected(old('legacy_role') === 'responder')>{{ $roleLabels['responder'] ?? 'responder' }}</option>
                <option value="reviewer" @selected(old('legacy_role') === 'reviewer')>{{ $roleLabels['reviewer'] ?? 'reviewer' }}</option>
            </select>
        </div>

        <div class="field full">
            <label>الأدوار</label>
            <div class="actions">
                @php($currentRoles = old('roles', []))
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
            @php($currentScopes = old('responder_scopes', ['all']))
            <div class="actions">
                @foreach ($scopeLabels as $scopeValue => $scopeLabel)
                    <label class="btn" style="cursor:pointer;">
                        <input type="checkbox" name="responder_scopes[]" value="{{ $scopeValue }}" @checked(in_array($scopeValue, $currentScopes, true))>
                        {{ $scopeLabel }}
                    </label>
                @endforeach
            </div>
            <span class="muted">إذا كان المستخدم مجيبًا فستظهر له فقط الاستفسارات المطابقة لهذه الاختصاصات.</span>
        </div>

        <div class="field full">
            <label>الصلاحيات المباشرة</label>

            @php($currentPermissions = old('permissions', []))
            @foreach ($modulePermissions as $moduleDisplayName => $modulePermissionItems)
                <div style="margin-bottom: 12px; border: 1px solid rgba(15, 23, 42, 0.12); border-radius: 12px; padding: 10px; background: rgba(255,255,255,0.5);">
                    <div class="muted" style="font-weight: 700; margin-bottom: 8px; color: #1f2937;">{{ $moduleDisplayName }}</div>
                    <div class="actions">
                        @foreach ($modulePermissionItems as $permissionName => $displayName)
                            <label class="btn" style="cursor:pointer;">
                                <input type="checkbox" name="permissions[]" value="{{ $permissionName }}" @checked(in_array($permissionName, $currentPermissions, true))>
                                {{ $permissionLabels[$permissionName] ?? $displayName }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="field full">
            <div class="actions">
                <button class="btn primary" type="submit">حفظ الصلاحيات</button>
            </div>
        </div>
    </form>
@endsection
