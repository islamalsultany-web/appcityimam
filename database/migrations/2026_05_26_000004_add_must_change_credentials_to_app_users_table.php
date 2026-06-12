<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->boolean('must_change_credentials')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->dropColumn('must_change_credentials');
        });
    }
};
