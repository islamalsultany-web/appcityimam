<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$role = \Spatie\Permission\Models\Role::where('name', 'asker')->with('permissions')->first();
if ($role) {
    echo "الدور: " . $role->display_name . "\n";
    echo "الصلاحيات:\n";
    foreach($role->permissions as $perm) {
        echo "  ✓ " . $perm->name . "\n";
    }
} else {
    echo "الدور غير موجود\n";
}
