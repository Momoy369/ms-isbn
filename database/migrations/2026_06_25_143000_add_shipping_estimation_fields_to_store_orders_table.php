<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->string('shipping_destination_city_id', 32)->nullable()->after('shipping_address');
            $table->string('shipping_service', 64)->nullable()->after('shipping_destination_city_id');
            $table->decimal('shipping_cost', 15, 2)->nullable()->after('shipping_service');
            $table->string('shipping_etd', 32)->nullable()->after('shipping_cost');
        });
    }

    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_destination_city_id',
                'shipping_service',
                'shipping_cost',
                'shipping_etd',
            ]);
        });
    }
};
