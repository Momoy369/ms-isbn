<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('author_ktp_number', 32)->nullable()->after('penulis_1')->index();
            $table->timestamp('claimed_at')->nullable()->after('author_ktp_number');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['author_ktp_number', 'claimed_at']);
        });
    }
};
