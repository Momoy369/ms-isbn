<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('author_invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number')->unique();

            $table->foreignId('book_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->comment('Author user id')
                ->constrained()
                ->cascadeOnDelete();

            // 'package'  = biaya paket penerbitan
            // 'revision' = revisi ke-2 dst pada satu tahap (berbayar)
            // 'additional' = layanan tambahan
            $table->enum('type', ['package', 'revision', 'additional'])
                ->default('package');

            $table->string('description');

            $table->decimal('amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'cancelled'])
                ->default('pending');

            $table->string('revision_stage')->nullable()
                ->comment('editing | layout | cover - hanya untuk tipe revision');

            $table->unsignedTinyInteger('revision_count')->default(1)
                ->comment('Ke-berapa revisi pada stage ini');

            $table->date('due_date')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->string('payment_proof')->nullable()
                ->comment('Path bukti pembayaran');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_invoices');
    }
};
