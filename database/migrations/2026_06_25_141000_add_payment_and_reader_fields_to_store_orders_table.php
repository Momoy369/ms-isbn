<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('payment_method', 32)->nullable()->after('status');
            $table->string('payment_gateway', 32)->nullable()->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_gateway');
            $table->string('gateway_reference')->nullable()->after('payment_reference');
            $table->string('gateway_checkout_url')->nullable()->after('gateway_reference');
            $table->timestamp('gateway_expires_at')->nullable()->after('gateway_checkout_url');
            $table->timestamp('paid_at')->nullable()->after('gateway_expires_at');
            $table->string('tracking_number')->nullable()->after('paid_at');
            $table->string('shipping_courier', 64)->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('shipping_courier');
            $table->string('reader_password_hash')->nullable()->after('shipped_at');
            $table->timestamp('reader_access_granted_at')->nullable()->after('reader_password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'payment_method',
                'payment_gateway',
                'payment_reference',
                'gateway_reference',
                'gateway_checkout_url',
                'gateway_expires_at',
                'paid_at',
                'tracking_number',
                'shipping_courier',
                'shipped_at',
                'reader_password_hash',
                'reader_access_granted_at',
            ]);
        });
    }
};
