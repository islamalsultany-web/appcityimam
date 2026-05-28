<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('app_users', 'password_confirmation')) {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->dropColumn('password_confirmation');
            });
        }
    }

    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->string('password_confirmation')->nullable()->after('password');
        });
    }
};
