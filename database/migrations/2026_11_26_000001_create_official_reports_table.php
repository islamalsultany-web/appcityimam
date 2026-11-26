<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('app_users')->cascadeOnDelete();
            $table->string('title');
            $table->string('division');
            $table->string('frequency', 10);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->json('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_reports');
    }
};
