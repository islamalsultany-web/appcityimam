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
            <option value="admin" @selected($currentRole === 'admin')>مسؤول</option>
        </select>
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
