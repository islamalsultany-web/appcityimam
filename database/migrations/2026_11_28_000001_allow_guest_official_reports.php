<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('official_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('guest_key', 64)->nullable()->index();
        });
    }

    public function down(): void
    {
        if (DB::table('official_reports')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Assign guest reports to users before rolling back guest report support.');
        }
        Schema::table('official_reports', function (Blueprint $table) {
            $table->dropColumn('guest_key');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
