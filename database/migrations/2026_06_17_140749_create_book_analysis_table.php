<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'book_analysis',
            function ($table) {

                $table->id();

                $table->foreignId('book_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->longText('full_text')
                    ->nullable();

                $table->longText('kata_pengantar')
                    ->nullable();

                $table->longText('daftar_isi')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_analysis');
    }
};
