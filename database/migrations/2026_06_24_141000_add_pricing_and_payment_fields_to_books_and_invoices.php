<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('selling_price', 12, 2)->nullable()->after('jumlah_halaman');
        });

        Schema::table('author_invoices', function (Blueprint $table) {
            $table->string('payment_gateway', 32)->nullable()->after('payment_method');
            $table->string('gateway_reference')->nullable()->after('payment_gateway');
            $table->string('gateway_checkout_url')->nullable()->after('gateway_reference');
            $table->timestamp('gateway_expires_at')->nullable()->after('gateway_checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('author_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'gateway_reference',
                'gateway_checkout_url',
                'gateway_expires_at',
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['selling_price']);
        });
    }
};
