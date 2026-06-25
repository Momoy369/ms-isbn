<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
            $table->foreignId('legacy_book_id')->nullable()->constrained('legacy_books')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('author_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('list_price', 12, 2)->default(0);
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('cover_image_path')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_featured']);
            $table->index(['book_id', 'legacy_book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_catalog_items');
    }
};
