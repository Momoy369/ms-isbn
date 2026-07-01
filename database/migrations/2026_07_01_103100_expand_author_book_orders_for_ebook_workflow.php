<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->string('ebook_platform', 120)->nullable()->after('shipping_notes');
            $table->string('ebook_publication_link', 2000)->nullable()->after('ebook_platform');
            $table->timestamp('ebook_submitted_at')->nullable()->after('ebook_publication_link');
            $table->timestamp('ebook_published_at')->nullable()->after('ebook_submitted_at');
        });

        DB::statement("ALTER TABLE author_book_orders MODIFY order_type ENUM('new_package','reprint','ebook_publication') NOT NULL");
        DB::statement("ALTER TABLE author_book_orders MODIFY status ENUM('pending','invoiced','paid','revision_requested','printing','print_completed','shipping','shipped','delivered','processing','completed','ebook_revision_requested','ebook_publishing','ebook_published','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE author_book_orders MODIFY status ENUM('pending','invoiced','paid','revision_requested','printing','print_completed','shipping','shipped','delivered','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE author_book_orders MODIFY order_type ENUM('new_package','reprint') NOT NULL");

        Schema::table('author_book_orders', function (Blueprint $table) {
            $table->dropColumn(['ebook_platform', 'ebook_publication_link', 'ebook_submitted_at', 'ebook_published_at']);
        });
    }
};
