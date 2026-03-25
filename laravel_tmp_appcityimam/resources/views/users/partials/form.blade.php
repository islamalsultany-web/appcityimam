<div>
    <label>اسم المستخدم</label>
    <input name="username" value="{{ old('username', $user?->username) }}" required>
</div>
<div>
    <label>كلمة المرور</label>
    <input name="password" type="password" required>
</div>
<div>
    <label>تأكيد كلمة المرور</label>
    <input name="password_confirmation" type="password" required>
</div>
<div>
    <label>الرقم الوظيفي</label>
    <input name="employee_number" value="{{ old('employee_number', $user?->employee_number) }}">
</div>
<div>
    <label>رقم الباج</label>
    <input name="badge_number" value="{{ old('badge_number', $user?->badge_number) }}">
</div>
<div>
    <label>الشعبة</label>
    <input name="division" value="{{ old('division', $user?->division) }}">
</div>
<div>
    <label>الوحدة</label>
    <input name="unit" value="{{ old('unit', $user?->unit) }}">
</div>
<div>
    <label>الدور</label>
    <select name="role" required>
        @php($currentRole = old('role', $user?->role ?? 'asker'))
        <option value="asker" @selected($currentRole === 'asker')>مستفسر</option>
        <option value="responder" @selected($currentRole === 'responder')>مجيب</option>
        <option value="admin" @selected($currentRole === 'admin')>مسؤول</option>
    </select>
</div>
