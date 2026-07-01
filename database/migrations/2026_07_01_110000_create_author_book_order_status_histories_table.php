<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('author_book_order_status_histories')) {
            return;
        }

        Schema::create('author_book_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_book_order_id')->constrained('author_book_orders')->cascadeOnDelete();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('context', 64)->nullable();
            $table->string('from_status', 64)->nullable();
            $table->string('to_status', 64);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['author_book_order_id', 'created_at'], 'aobosh_order_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_book_order_status_histories');
    }
};
