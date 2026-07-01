<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_package_consultations', function (Blueprint $table) {
            $table->text('finance_notes')->nullable()->after('notes');
            $table->date('next_action_at')->nullable()->after('finance_notes');

            $table->index(['next_action_at', 'status'], 'spc_next_action_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('store_package_consultations', function (Blueprint $table) {
            $table->dropIndex('spc_next_action_status_idx');
            $table->dropColumn(['finance_notes', 'next_action_at']);
        });
    }
};
