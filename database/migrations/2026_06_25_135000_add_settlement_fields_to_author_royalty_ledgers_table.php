<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('author_royalty_ledgers', function (Blueprint $table) {
            $table->decimal('platform_fee_amount', 14, 2)->default(0)->after('gross_amount');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('platform_fee_amount');
            $table->decimal('returns_reserve_amount', 14, 2)->default(0)->after('tax_amount');
            $table->decimal('net_royalty_amount', 14, 2)->default(0)->after('royalty_amount');
        });
    }

    public function down(): void
    {
        Schema::table('author_royalty_ledgers', function (Blueprint $table) {
            $table->dropColumn([
                'platform_fee_amount',
                'tax_amount',
                'returns_reserve_amount',
                'net_royalty_amount',
            ]);
        });
    }
};
