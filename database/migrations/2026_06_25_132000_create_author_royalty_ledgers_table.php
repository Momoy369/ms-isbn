<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('author_royalty_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('royalty_rate', 5, 4)->default(0.20);
            $table->decimal('royalty_amount', 14, 2)->default(0);
            $table->string('status')->default('accrued');
            $table->foreignId('payout_request_id')->nullable()->constrained('author_royalty_payout_requests')->nullOnDelete();
            $table->timestamp('generated_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['author_user_id', 'book_id', 'period_start', 'period_end'], 'author_royalty_ledger_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_royalty_ledgers');
    }
};
