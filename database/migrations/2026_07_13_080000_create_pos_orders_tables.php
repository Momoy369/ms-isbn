<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('source_channel', 50)->default('offline');
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('confirmed');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['source_channel']);
        });

        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->enum('item_type', ['publishing_service', 'book_print', 'ebook']);
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['pos_order_id', 'item_type']);
        });

        Schema::create('pos_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('installment_number')->comment('1 untuk termin 1, 2 untuk termin 2');
            $table->string('description');
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 32)->nullable();
            $table->string('payment_reference')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['pos_order_id', 'installment_number']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_invoices');
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
    }
};
