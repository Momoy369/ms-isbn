<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        DB::statement("
            ALTER TABLE books
            MODIFY COLUMN book_type
            ENUM(
                'fiction',
                'nonfiction',
                'poetry'
            )
            DEFAULT 'fiction'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE books
            MODIFY COLUMN book_type
            ENUM(
                'fiction',
                'nonfiction'
            )
            DEFAULT 'fiction'
        ");
    }
};