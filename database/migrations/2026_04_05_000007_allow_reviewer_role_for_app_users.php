<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE app_users MODIFY role ENUM('asker','responder','reviewer','admin') NOT NULL DEFAULT 'asker'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE app_users MODIFY role ENUM('asker','responder','admin') NOT NULL DEFAULT 'asker'");
        }
    }
};
