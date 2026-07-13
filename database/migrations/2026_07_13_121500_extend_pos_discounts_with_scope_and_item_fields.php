<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->enum('discount_scope', ['global', 'unit', 'item'])
                ->default('global')
                ->after('status');
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->enum('discount_type', ['nominal', 'percent'])
                ->default('nominal')
                ->after('unit_price');
            $table->decimal('discount_input', 12, 2)
                ->default(0)
                ->after('discount_type');
            $table->decimal('discount_amount', 12, 2)
                ->default(0)
                ->after('discount_input');
            $table->decimal('line_total_before_discount', 12, 2)
                ->default(0)
                ->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_input',
                'discount_amount',
                'line_total_before_discount',
            ]);
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn('discount_scope');
        });
    }
};
