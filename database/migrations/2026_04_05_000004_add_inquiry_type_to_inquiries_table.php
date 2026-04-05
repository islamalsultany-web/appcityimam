<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('inquiries', 'inquiry_type')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->enum('inquiry_type', ['financial', 'administrative', 'technical', 'warehouse', 'other'])
                    ->default('other')
                    ->after('title');
            });
        }

        if (Schema::hasColumn('inquiries', 'category')) {
            DB::statement("UPDATE inquiries SET inquiry_type = CASE category
                WHEN 'financial' THEN 'financial'
                WHEN 'administrative' THEN 'administrative'
                WHEN 'technical' THEN 'technical'
                ELSE 'other'
            END");

            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inquiries', 'inquiry_type')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropColumn('inquiry_type');
            });
        }
    }
};
