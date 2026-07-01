<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_files', function (Blueprint $table) {
            $table->json('allowed_roles')->nullable()->after('access_scope');
            $table->json('allowed_emails')->nullable()->after('allowed_roles');
            $table->json('allowed_domains')->nullable()->after('allowed_emails');
        });
    }

    public function down(): void
    {
        Schema::table('role_files', function (Blueprint $table) {
            $table->dropColumn(['allowed_roles', 'allowed_emails', 'allowed_domains']);
        });
    }
};
