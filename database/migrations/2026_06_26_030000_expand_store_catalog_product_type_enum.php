<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE store_catalog_items MODIFY COLUMN product_type ENUM('print','ebook','print_ebook') NOT NULL DEFAULT 'print'");
    }

    public function down(): void
    {
        DB::table('store_catalog_items')
            ->where('product_type', 'print_ebook')
            ->update(['product_type' => 'print']);

        DB::statement("ALTER TABLE store_catalog_items MODIFY COLUMN product_type ENUM('print','ebook') NOT NULL DEFAULT 'print'");
    }
};
