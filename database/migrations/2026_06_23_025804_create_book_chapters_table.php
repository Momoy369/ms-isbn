<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_chapters', function (Blueprint $table) {

            $table->id();

            $table->foreignId('book_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('chapter_order');

            $table->string('title');

            $table->longText('content');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_chapters');
    }
};