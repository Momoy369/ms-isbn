<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('personal_board_cards', function (Blueprint $table) {
            $table->string('priority', 10)
                ->default('medium')
                ->after('board_column');

            $table->index(['user_id', 'priority'], 'personal_board_cards_user_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::table('personal_board_cards', function (Blueprint $table) {
            $table->dropIndex('personal_board_cards_user_priority_idx');
            $table->dropColumn('priority');
        });
    }
};
