<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('store_catalog_item_id')->constrained('store_vouchers')->nullOnDelete();
            $table->string('voucher_code', 64)->nullable()->after('voucher_id');
            $table->string('voucher_name')->nullable()->after('voucher_code');
            $table->decimal('voucher_discount_amount', 12, 2)->default(0)->after('shipping_cost');
            $table->decimal('subtotal_before_discount', 12, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn([
                'voucher_code',
                'voucher_name',
                'voucher_discount_amount',
                'subtotal_before_discount',
            ]);
        });
    }
};