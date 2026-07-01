<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedInteger('manuscript_a5_pages')->nullable()->after('manuscript_a4_pages');
            $table->unsignedInteger('manuscript_print_overage_pages')->default(0)->after('manuscript_overage_pages');
            $table->decimal('manuscript_print_overage_fee', 12, 2)->default(0)->after('manuscript_editing_overage_fee');
        });

        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->unsignedInteger('manuscript_a5_pages')->nullable()->after('manuscript_a4_pages');
            $table->unsignedInteger('a5_page_limit')->default(100)->after('a4_page_limit');
            $table->unsignedInteger('print_over_limit_pages')->default(0)->after('over_limit_pages');
            $table->decimal('print_over_limit_fee', 12, 2)->default(0)->after('editing_over_limit_fee');
        });
    }

    public function down(): void
    {
        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->dropColumn([
                'manuscript_a5_pages',
                'a5_page_limit',
                'print_over_limit_pages',
                'print_over_limit_fee',
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'manuscript_a5_pages',
                'manuscript_print_overage_pages',
                'manuscript_print_overage_fee',
            ]);
        });
    }
};
