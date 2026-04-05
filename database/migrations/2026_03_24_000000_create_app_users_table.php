<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('password_confirmation')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('badge_number')->nullable();
            $table->string('division')->nullable();
            $table->string('unit')->nullable();
            $table->enum('role', ['asker', 'responder', 'reviewer', 'admin'])->default('asker');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
