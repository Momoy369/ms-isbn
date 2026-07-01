<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_file_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_file_id')->constrained('role_files')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('role', 32)->nullable()->index();
            $table->string('action', 40)->index();
            $table->boolean('granted')->default(false)->index();
            $table->string('scope', 20)->nullable();
            $table->string('note', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_file_access_logs');
    }
};
