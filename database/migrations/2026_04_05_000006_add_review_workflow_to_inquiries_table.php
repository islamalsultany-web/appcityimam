<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('inquiries', 'review_status')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->enum('review_status', ['pending_review', 'approved', 'returned'])
                    ->nullable()
                    ->after('response_body');
                $table->text('review_note')->nullable()->after('review_status');
                $table->foreignId('reviewed_by_user_id')
                    ->nullable()
                    ->after('review_note')
                    ->constrained('app_users')
                    ->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
            });
        }

        DB::table('inquiries')
            ->whereNotNull('response_body')
            ->where('response_body', '!=', '')
            ->whereNull('review_status')
            ->update([
                'review_status' => 'approved',
                'reviewed_at' => DB::raw('responded_at'),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('inquiries', 'reviewed_by_user_id')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by_user_id');
            });
        }

        Schema::table('inquiries', function (Blueprint $table) {
            foreach (['reviewed_at', 'review_note', 'review_status'] as $column) {
                if (Schema::hasColumn('inquiries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
