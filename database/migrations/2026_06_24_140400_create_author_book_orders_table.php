<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('author_book_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
            $table->foreignId('publishing_package_id')->nullable()->constrained('publishing_packages')->nullOnDelete();
            $table->foreignId('print_price_rule_id')->nullable()->constrained('print_price_rules')->nullOnDelete();
            $table->foreignId('author_invoice_id')->nullable()->constrained('author_invoices')->nullOnDelete();

            $table->enum('order_type', ['new_package', 'reprint']);
            $table->string('title')->nullable();
            $table->unsignedInteger('pages')->default(0);
            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->string('destination_province')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('destination_city_id')->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->text('shipping_address')->nullable();

            $table->string('courier', 32)->nullable();
            $table->string('courier_service', 64)->nullable();
            $table->string('etd')->nullable();
            $table->json('shipping_payload')->nullable();

            $table->enum('status', ['pending', 'invoiced', 'paid', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_book_orders');
    }
};
