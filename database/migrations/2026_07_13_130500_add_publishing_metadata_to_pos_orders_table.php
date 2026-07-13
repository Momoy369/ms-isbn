<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->json('publishing_metadata')
                ->nullable()
                ->after('marketing_ref');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn('publishing_metadata');
        });
    }
};
