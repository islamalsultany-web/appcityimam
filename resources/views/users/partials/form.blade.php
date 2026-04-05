<div class="form-grid">
    <div class="field">
        <label for="username">اسم المستخدم</label>
        <input id="username" name="username" value="{{ old('username', $user?->username) }}" required>
    </div>

    <div class="field">
        <label for="role">الدور</label>
        <select id="role" name="role" required>
            @php($currentRole = old('role', $user?->role ?? 'asker'))
            <option value="asker" @selected($currentRole === 'asker')>مستفسر</option>
            <option value="responder" @selected($currentRole === 'responder')>مجيب</option>
            <option value="reviewer" @selected($currentRole === 'reviewer')>مدقق</option>
            <option value="admin" @selected($currentRole === 'admin')>مسؤول</option>
        </select>
    </div>

    <div class="field full" id="responderScopesField">
        <label>اختصاصات المجيب</label>
        @php($scopeLabels = \App\Models\AppUser::RESPONDER_SCOPE_LABELS)
        @php($currentScopes = old('responder_scopes', $user?->normalizedResponderScopes() ?? (in_array($currentRole, ['responder', 'admin'], true) ? ['all'] : [])))
        <div class="actions">
            @foreach ($scopeLabels as $scopeValue => $scopeLabel)
                <label class="btn" style="cursor:pointer;">
                    <input type="checkbox" name="responder_scopes[]" value="{{ $scopeValue }}" @checked(in_array($scopeValue, $currentScopes, true))>
                    {{ $scopeLabel }}
                </label>
            @endforeach
        </div>
        <span class="muted">تُستخدم للمجيب أو المسؤول فقط. إذا تم اختيار "كل الأنواع" فستظهر له جميع الاستفسارات.</span>
    </div>

    <div class="field">
        <label for="password">كلمة المرور</label>
        <input id="password" name="password" type="password" required>
    </div>

    <div class="field">
        <label for="password_confirmation">تأكيد كلمة المرور</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>
    </div>

    <div class="field">
        <label for="employee_number">الرقم الوظيفي</label>
        <input id="employee_number" name="employee_number" value="{{ old('employee_number', $user?->employee_number) }}">
    </div>

    <div class="field">
        <label for="badge_number">رقم الباج</label>
        <input id="badge_number" name="badge_number" value="{{ old('badge_number', $user?->badge_number) }}">
    </div>

    <div class="field">
        <label for="division">الشعبة</label>
        <input id="division" name="division" value="{{ old('division', $user?->division) }}">
    </div>

    <div class="field">
        <label for="unit">الوحدة</label>
        <input id="unit" name="unit" value="{{ old('unit', $user?->unit) }}">
    </div>
</div>

<script>
    (function () {
        var roleSelect = document.getElementById('role');
        var scopesField = document.getElementById('responderScopesField');
        if (!roleSelect || !scopesField) {
            return;
        }

        var toggleScopes = function () {
            var visible = roleSelect.value === 'responder' || roleSelect.value === 'admin';
            scopesField.style.display = visible ? '' : 'none';
        };

        roleSelect.addEventListener('change', toggleScopes);
        toggleScopes();
    })();
</script>
