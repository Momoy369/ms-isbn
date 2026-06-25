<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('external_sales_records', function (Blueprint $table) {
            $table->foreignId('legacy_book_id')->nullable()->after('book_id')->constrained('legacy_books')->nullOnDelete();
        });

        DB::statement('ALTER TABLE external_sales_records DROP FOREIGN KEY external_sales_records_book_id_foreign');
        DB::statement('ALTER TABLE external_sales_records MODIFY book_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE external_sales_records ADD CONSTRAINT external_sales_records_book_id_foreign FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE external_sales_records DROP FOREIGN KEY external_sales_records_book_id_foreign');
        DB::statement('ALTER TABLE external_sales_records MODIFY book_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE external_sales_records ADD CONSTRAINT external_sales_records_book_id_foreign FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE');

        Schema::table('external_sales_records', function (Blueprint $table) {
            $table->dropForeign(['legacy_book_id']);
            $table->dropColumn('legacy_book_id');
        });
    }
};
