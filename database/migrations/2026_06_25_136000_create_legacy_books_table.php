<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legacy_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('author_name');
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('isbn')->nullable();
            $table->unsignedInteger('published_year')->nullable();
            $table->decimal('list_price', 12, 2)->default(0);
            $table->boolean('royalty_enabled')->default(false);
            $table->decimal('royalty_rate', 5, 4)->nullable();
            $table->boolean('distribution_online')->default(false);
            $table->boolean('distribution_ebook')->default(false);
            $table->boolean('distribution_marketplace')->default(false);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_books');
    }
};
