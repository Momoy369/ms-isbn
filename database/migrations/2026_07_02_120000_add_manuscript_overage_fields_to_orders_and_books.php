<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedInteger('manuscript_a4_pages')->nullable()->after('jumlah_halaman');
            $table->unsignedInteger('manuscript_overage_pages')->default(0)->after('manuscript_a4_pages');
            $table->decimal('manuscript_layout_overage_fee', 12, 2)->default(0)->after('manuscript_overage_pages');
            $table->decimal('manuscript_editing_overage_fee', 12, 2)->default(0)->after('manuscript_layout_overage_fee');
            $table->decimal('package_extra_fee', 12, 2)->default(0)->after('manuscript_editing_overage_fee');
        });

        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->unsignedInteger('manuscript_a4_pages')->nullable()->after('pages');
            $table->unsignedInteger('a4_page_limit')->default(125)->after('manuscript_a4_pages');
            $table->unsignedInteger('over_limit_pages')->default(0)->after('a4_page_limit');
            $table->decimal('layout_over_limit_fee', 12, 2)->default(0)->after('over_limit_pages');
            $table->decimal('editing_over_limit_fee', 12, 2)->default(0)->after('layout_over_limit_fee');
        });
    }

    public function down(): void
    {
        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->dropColumn([
                'manuscript_a4_pages',
                'a4_page_limit',
                'over_limit_pages',
                'layout_over_limit_fee',
                'editing_over_limit_fee',
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'manuscript_a4_pages',
                'manuscript_overage_pages',
                'manuscript_layout_overage_fee',
                'manuscript_editing_overage_fee',
                'package_extra_fee',
            ]);
        });
    }
};
