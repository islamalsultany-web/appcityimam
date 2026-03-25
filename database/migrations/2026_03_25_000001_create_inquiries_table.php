<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asker_user_id')->constrained('app_users')->cascadeOnDelete();
            $table->string('title');
            $table->enum('priority', ['normal', 'urgent', 'very_urgent'])->default('normal');
            $table->enum('preferred_channel', ['system', 'phone', 'email'])->default('system');
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'answered', 'needs_info', 'closed'])->default('pending');
            $table->enum('response_type', ['final', 'partial', 'request_info'])->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('response_body')->nullable();
            $table->text('internal_note')->nullable();
            $table->string('response_attachment_path')->nullable();
            $table->foreignId('responder_user_id')->nullable()->constrained('app_users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
