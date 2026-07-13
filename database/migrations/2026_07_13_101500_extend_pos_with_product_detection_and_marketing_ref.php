<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->string('service_order_ref')->nullable()->after('author_ktp_number');
            $table->string('marketing_ref')->nullable()->after('service_order_ref');

            $table->index(['service_order_ref']);
            $table->index(['marketing_ref']);
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->string('product_source_type', 50)->nullable()->after('publishing_package_id');
            $table->unsignedBigInteger('product_source_id')->nullable()->after('product_source_type');

            $table->index(['product_source_type', 'product_source_id'], 'pos_items_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropIndex('pos_items_source_idx');
            $table->dropColumn([
                'product_source_type',
                'product_source_id',
            ]);
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropIndex(['service_order_ref']);
            $table->dropIndex(['marketing_ref']);
            $table->dropColumn([
                'service_order_ref',
                'marketing_ref',
            ]);
        });
    }
};
