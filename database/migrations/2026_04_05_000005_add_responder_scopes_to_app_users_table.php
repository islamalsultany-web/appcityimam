<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('app_users', 'responder_scopes')) {
            Schema::table('app_users', function (Blueprint $table) {
                $table->json('responder_scopes')->nullable()->after('role');
            });
        }

        DB::table('app_users')
            ->whereIn('role', ['responder', 'admin'])
            ->whereNull('responder_scopes')
            ->update(['responder_scopes' => json_encode(['all'])]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_users', 'responder_scopes')) {
            Schema::table('app_users', function (Blueprint $table) {
                $table->dropColumn('responder_scopes');
            });
        }
    }
};
