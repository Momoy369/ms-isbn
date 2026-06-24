<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->text('final_drive_link')->nullable()->after('link_produk');
            $table->text('final_ebook_link')->nullable()->after('final_drive_link');
            $table->boolean('links_unlocked_manually')->default(false)->after('claimed_at');
            $table->timestamp('links_unlocked_at')->nullable()->after('links_unlocked_manually');
            $table->foreignId('links_unlocked_by_user_id')->nullable()->after('links_unlocked_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('links_unlocked_by_user_id');
            $table->dropColumn([
                'final_drive_link',
                'final_ebook_link',
                'links_unlocked_manually',
                'links_unlocked_at',
            ]);
        });
    }
};
