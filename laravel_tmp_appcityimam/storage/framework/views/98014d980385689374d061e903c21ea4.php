<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء مستخدم</title>
</head>
<body>
    <h1>إنشاء مستخدم</h1>
    <p><a href="<?php echo e(route('users.index')); ?>">رجوع</a></p>

    <form method="POST" action="<?php echo e(route('users.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('users.partials.form', ['user' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button type="submit">حفظ</button>
    </form>
</body>
</html>
<?php /**PATH D:\لوحة البرامج\appcityimam\laravel_tmp_appcityimam\resources\views/users/create.blade.php ENDPATH**/ ?>