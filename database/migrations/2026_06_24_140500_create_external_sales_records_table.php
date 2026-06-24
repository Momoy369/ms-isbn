<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_sales_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('input_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('channel', ['amazon', 'google_play_books', 'website', 'marketplace', 'other']);
            $table->enum('format', ['ebook', 'print']);
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->date('sold_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_sales_records');
    }
};
