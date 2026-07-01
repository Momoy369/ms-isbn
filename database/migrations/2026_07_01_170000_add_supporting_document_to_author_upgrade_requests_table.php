<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('author_upgrade_requests', function (Blueprint $table): void {
            if (!Schema::hasColumn('author_upgrade_requests', 'supporting_document_path')) {
                $table->string('supporting_document_path')->nullable()->after('request_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('author_upgrade_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('author_upgrade_requests', 'supporting_document_path')) {
                $table->dropColumn('supporting_document_path');
            }
        });
    }
};
