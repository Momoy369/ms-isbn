<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('author_royalty_payout_requests', function (Blueprint $table) {
            $table->foreignId('approved_by_user_id')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            $table->foreignId('paid_by_user_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->string('transfer_reference')->nullable()->after('paid_by_user_id');
            $table->string('transfer_proof_file_path')->nullable()->after('transfer_reference');
        });
    }

    public function down(): void
    {
        Schema::table('author_royalty_payout_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropForeign(['paid_by_user_id']);
            $table->dropColumn([
                'approved_by_user_id',
                'approved_at',
                'paid_by_user_id',
                'transfer_reference',
                'transfer_proof_file_path',
            ]);
        });
    }
};
