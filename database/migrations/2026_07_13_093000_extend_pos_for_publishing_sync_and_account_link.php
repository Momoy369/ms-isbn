<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->foreignId('linked_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('linked_book_id')->nullable()->after('linked_user_id')->constrained('books')->nullOnDelete();
            $table->string('manuscript_title')->nullable()->after('customer_email');
            $table->string('author_ktp_number', 32)->nullable()->after('manuscript_title');
            $table->timestamp('production_synced_at')->nullable()->after('linked_book_id');

            $table->index(['linked_user_id']);
            $table->index(['linked_book_id']);
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->foreignId('publishing_package_id')->nullable()->after('item_type')->constrained('publishing_packages')->nullOnDelete();
            $table->decimal('extra_service_amount', 12, 2)->default(0)->after('line_total');
        });

        // Add `extra_service` item type while keeping existing enum values.
        DB::statement("ALTER TABLE pos_order_items MODIFY item_type ENUM('publishing_service','book_print','ebook','extra_service') NOT NULL");
    }

    public function down(): void
    {
        // Revert enum before dropping columns.
        DB::statement("ALTER TABLE pos_order_items MODIFY item_type ENUM('publishing_service','book_print','ebook') NOT NULL");

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publishing_package_id');
            $table->dropColumn('extra_service_amount');
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropIndex(['linked_user_id']);
            $table->dropIndex(['linked_book_id']);
            $table->dropConstrainedForeignId('linked_book_id');
            $table->dropConstrainedForeignId('linked_user_id');
            $table->dropColumn([
                'manuscript_title',
                'author_ktp_number',
                'production_synced_at',
            ]);
        });
    }
};
