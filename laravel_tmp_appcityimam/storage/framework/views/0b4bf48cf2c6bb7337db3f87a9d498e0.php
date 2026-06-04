<div>
    <label>اسم المستخدم</label>
    <input name="username" value="<?php echo e(old('username', $user?->username)); ?>" required>
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
    <input name="employee_number" value="<?php echo e(old('employee_number', $user?->employee_number)); ?>">
</div>
<div>
    <label>رقم الباج</label>
    <input name="badge_number" value="<?php echo e(old('badge_number', $user?->badge_number)); ?>">
</div>
<div>
    <label>الشعبة</label>
    <input name="division" value="<?php echo e(old('division', $user?->division)); ?>">
</div>
<div>
    <label>الوحدة</label>
    <input name="unit" value="<?php echo e(old('unit', $user?->unit)); ?>">
</div>
<div>
    <label>الدور</label>
    <select name="role" required>
        <?php ($currentRole = old('role', $user?->role ?? 'asker')); ?>
        <option value="asker" <?php if($currentRole === 'asker'): echo 'selected'; endif; ?>>مستفسر</option>
        <option value="responder" <?php if($currentRole === 'responder'): echo 'selected'; endif; ?>>مجيب</option>
        <option value="admin" <?php if($currentRole === 'admin'): echo 'selected'; endif; ?>>مسؤول</option>
    </select>
</div>
<?php /**PATH D:\لوحة البرامج\appcityimam\laravel_tmp_appcityimam\resources\views/users/partials/form.blade.php ENDPATH**/ ?>