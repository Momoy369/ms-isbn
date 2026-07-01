<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('book_sections', function (Blueprint $table): void {
            if (!$this->indexExists('book_sections', 'idx_book_sections_book_type')) {
                $table->index(['book_id', 'section_type'], 'idx_book_sections_book_type');
            }
        });

        Schema::table('book_files', function (Blueprint $table): void {
            if (!$this->indexExists('book_files', 'idx_book_files_book_type_active')) {
                $table->index(['book_id', 'type', 'is_active'], 'idx_book_files_book_type_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_sections', function (Blueprint $table): void {
            if ($this->indexExists('book_sections', 'idx_book_sections_book_type')) {
                $table->dropIndex('idx_book_sections_book_type');
            }
        });

        Schema::table('book_files', function (Blueprint $table): void {
            if ($this->indexExists('book_files', 'idx_book_files_book_type_active')) {
                $table->dropIndex('idx_book_files_book_type_active');
            }
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $databaseName = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(1) AS aggregate_count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$databaseName, $tableName, $indexName]
        );

        return ((int) ($result->aggregate_count ?? 0)) > 0;
    }
};
