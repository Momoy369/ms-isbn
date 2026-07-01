<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_board_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 140);
            $table->text('content')->nullable();
            $table->enum('board_column', ['todo', 'scheduled', 'done'])->default('todo');
            $table->dateTime('due_at')->nullable();
            $table->unsignedInteger('card_order')->default(0);
            $table->string('color', 20)->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'board_column', 'card_order'], 'personal_board_cards_user_column_order_idx');
            $table->index(['user_id', 'is_archived'], 'personal_board_cards_user_archive_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_board_cards');
    }
};
