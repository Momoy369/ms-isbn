<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'assignment_histories',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'book_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'role'
                );

                $table->string(
                    'activity'
                );

                $table->string(
                    'old_person'
                )->nullable();

                $table->string(
                    'new_person'
                )->nullable();

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'assignment_histories'
        );
    }
};