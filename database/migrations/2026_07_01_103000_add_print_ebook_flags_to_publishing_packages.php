<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('publishing_packages', function (Blueprint $table) {
            $table->boolean('supports_print')->default(true)->after('description');
            $table->boolean('supports_ebook')->default(false)->after('supports_print');
        });
    }

    public function down(): void
    {
        Schema::table('publishing_packages', function (Blueprint $table) {
            $table->dropColumn(['supports_print', 'supports_ebook']);
        });
    }
};
