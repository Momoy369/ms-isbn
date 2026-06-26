<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_catalog_items', function (Blueprint $table) {
            // Harga khusus ebook saat item bertipe print_ebook. Jika null, gunakan list_price.
            $table->decimal('ebook_price', 12, 2)->nullable()->after('promo_price');
            $table->decimal('ebook_promo_price', 12, 2)->nullable()->after('ebook_price');
        });

        Schema::table('store_orders', function (Blueprint $table) {
            // Format yang dipilih customer saat checkout: 'print' | 'ebook'
            $table->string('selected_format', 16)->nullable()->after('store_catalog_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('store_catalog_items', function (Blueprint $table) {
            $table->dropColumn(['ebook_price', 'ebook_promo_price']);
        });

        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn('selected_format');
        });
    }
};
