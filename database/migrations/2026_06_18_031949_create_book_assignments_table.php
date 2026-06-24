<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'book_assignments',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('book_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('role');

                $table->string('person_name');

                $table->timestamp('assigned_at')
                    ->nullable();

                $table->timestamp('completed_at')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'book_assignments'
        );
    }
};