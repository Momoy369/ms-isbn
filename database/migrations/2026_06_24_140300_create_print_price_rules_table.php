<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('print_price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('paper_type');
            $table->string('paper_size')->nullable();
            $table->string('print_type')->default('blackwhite');
            $table->unsignedInteger('min_pages')->default(1);
            $table->unsignedInteger('max_pages')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('price_per_page', 12, 2)->default(0);
            $table->unsignedInteger('weight_per_copy_gram')->default(250);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_price_rules');
    }
};
