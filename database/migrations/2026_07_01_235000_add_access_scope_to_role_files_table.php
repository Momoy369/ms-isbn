<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_files', function (Blueprint $table) {
            $table->string('access_scope', 20)->default('role')->after('role')->index();
        });
    }

    public function down(): void
    {
        Schema::table('role_files', function (Blueprint $table) {
            $table->dropIndex(['access_scope']);
            $table->dropColumn('access_scope');
        });
    }
};
