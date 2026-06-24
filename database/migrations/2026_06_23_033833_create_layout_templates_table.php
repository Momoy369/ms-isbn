<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('layout_templates', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('paper_size')
                ->default('A5');

            $table->string('font_family')
                ->default('Times New Roman');

            $table->integer('font_size')
                ->default(11);

            $table->decimal('margin_top', 5, 2)
                ->default(2);

            $table->decimal('margin_bottom', 5, 2)
                ->default(2);

            $table->decimal('margin_left', 5, 2)
                ->default(2);

            $table->decimal('margin_right', 5, 2)
                ->default(2);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layout_templates');
    }
};
