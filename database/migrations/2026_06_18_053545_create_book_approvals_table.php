<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'book_approvals',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('book_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('approval_type');

                $table->string('approved_by')
                    ->nullable();

                $table->timestamp('approved_at')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'book_approvals'
        );
    }
};