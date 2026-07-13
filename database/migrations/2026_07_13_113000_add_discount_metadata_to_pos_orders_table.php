<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->enum('discount_type', ['nominal', 'percent'])
                ->default('nominal')
                ->after('subtotal');
            $table->decimal('discount_input', 12, 2)
                ->default(0)
                ->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_input']);
        });
    }
};
