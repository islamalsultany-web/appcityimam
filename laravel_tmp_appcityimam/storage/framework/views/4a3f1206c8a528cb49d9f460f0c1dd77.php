<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المستخدمون</title>
</head>
<body>
    <h1>المستخدمون</h1>

    <?php if(session('success')): ?>
        <p><?php echo e(session('success')); ?></p>
    <?php endif; ?>

    <p><a href="<?php echo e(route('users.create')); ?>">إنشاء مستخدم جديد</a></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>اسم المستخدم</th>
                <th>الرقم الوظيفي</th>
                <th>رقم الباج</th>
                <th>الشعبة</th>
                <th>الوحدة</th>
                <th>الدور</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($user->username); ?></td>
                    <td><?php echo e($user->employee_number); ?></td>
                    <td><?php echo e($user->badge_number); ?></td>
                    <td><?php echo e($user->division); ?></td>
                    <td><?php echo e($user->unit); ?></td>
                    <td><?php echo e($user->role); ?></td>
                    <td>
                        <a href="<?php echo e(route('users.show', $user)); ?>">عرض</a>
                        <a href="<?php echo e(route('users.edit', $user)); ?>">تعديل</a>
                        <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7">لا توجد بيانات</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php echo e($users->links()); ?>

</body>
</html>
<?php /**PATH D:\لوحة البرامج\appcityimam\laravel_tmp_appcityimam\resources\views/users/index.blade.php ENDPATH**/ ?>