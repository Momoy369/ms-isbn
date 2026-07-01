<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->timestamp('revision_requested_at')->nullable()->after('paid_at');
            $table->timestamp('print_started_at')->nullable()->after('revision_requested_at');
            $table->timestamp('print_completed_at')->nullable()->after('print_started_at');
            $table->timestamp('shipping_started_at')->nullable()->after('print_completed_at');
            $table->timestamp('shipped_at')->nullable()->after('shipping_started_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->string('tracking_number', 120)->nullable()->after('delivered_at');
            $table->text('shipping_notes')->nullable()->after('tracking_number');
        });

        DB::statement("ALTER TABLE author_book_orders MODIFY status ENUM('pending','invoiced','paid','revision_requested','printing','print_completed','shipping','shipped','delivered','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE author_book_orders MODIFY status ENUM('pending','invoiced','paid','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->dropColumn([
                'revision_requested_at',
                'print_started_at',
                'print_completed_at',
                'shipping_started_at',
                'shipped_at',
                'delivered_at',
                'tracking_number',
                'shipping_notes',
            ]);
        });
    }
};
