<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_catalog_items', function (Blueprint $table) {
            $table->enum('product_type', ['print', 'ebook'])->default('print')->after('author_name');
            $table->string('ebook_read_link')->nullable()->after('cover_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('store_catalog_items', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'ebook_read_link']);
        });
    }
};
