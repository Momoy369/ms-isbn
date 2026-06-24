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
        Schema::create('book_reviews', function (Blueprint $table) {

            $table->id();

            $table->foreignId('book_id');

            $table->string('stage');

            $table->enum('status', [

                'approved',

                'revision'

            ]);

            $table->text('note')->nullable();

            $table->string('attachment')->nullable();

            $table->foreignId('user_id');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_reviews');
    }
};
