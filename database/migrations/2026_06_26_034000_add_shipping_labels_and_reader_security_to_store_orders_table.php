<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->string('shipping_destination_province_id', 32)->nullable()->after('shipping_address');
            $table->string('shipping_destination_province_name', 120)->nullable()->after('shipping_destination_province_id');
            $table->string('shipping_destination_city_name', 120)->nullable()->after('shipping_destination_city_id');

            $table->string('reader_access_token_hash', 128)->nullable()->after('reader_password_hash');
            $table->timestamp('reader_access_token_expires_at')->nullable()->after('reader_access_token_hash');
            $table->string('reader_last_device_hash', 128)->nullable()->after('reader_access_token_expires_at');
            $table->string('reader_last_session_id', 128)->nullable()->after('reader_last_device_hash');
            $table->unsignedInteger('reader_active_sessions')->default(0)->after('reader_last_session_id');
            $table->timestamp('reader_last_used_at')->nullable()->after('reader_active_sessions');
        });
    }

    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_destination_province_id',
                'shipping_destination_province_name',
                'shipping_destination_city_name',
                'reader_access_token_hash',
                'reader_access_token_expires_at',
                'reader_last_device_hash',
                'reader_last_session_id',
                'reader_active_sessions',
                'reader_last_used_at',
            ]);
        });
    }
};
