<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->boolean('royalty_enabled')->default(false);
            $table->decimal('royalty_rate', 5, 4)->nullable();
            $table->boolean('royalty_distribution_online')->default(false);
            $table->boolean('royalty_distribution_ebook')->default(false);
            $table->boolean('royalty_distribution_marketplace')->default(false);
            $table->string('royalty_agreement_file_path')->nullable();
            $table->string('royalty_contract_file_path')->nullable();
            $table->text('royalty_notes')->nullable();
            $table->timestamp('royalty_enabled_at')->nullable();
            $table->foreignId('royalty_enabled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['royalty_enabled_by_user_id']);
            $table->dropColumn([
                'royalty_enabled',
                'royalty_rate',
                'royalty_distribution_online',
                'royalty_distribution_ebook',
                'royalty_distribution_marketplace',
                'royalty_agreement_file_path',
                'royalty_contract_file_path',
                'royalty_notes',
                'royalty_enabled_at',
                'royalty_enabled_by_user_id',
            ]);
        });
    }
};
