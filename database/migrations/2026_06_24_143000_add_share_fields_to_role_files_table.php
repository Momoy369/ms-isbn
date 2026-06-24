<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_files', function (Blueprint $table) {
            $table->string('share_token', 80)->nullable()->unique()->after('is_image');
            $table->timestamp('share_expires_at')->nullable()->after('share_token');
        });
    }

    public function down(): void
    {
        Schema::table('role_files', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn(['share_token', 'share_expires_at']);
        });
    }
};
