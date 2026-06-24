<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('books', function (Blueprint $table) {

        $table->id();

        $table->string('nomor_naskah')->unique();

        $table->string('judul');

        $table->string('subjudul')->nullable();

        $table->string('penulis_1');

        $table->string('penulis_2')->nullable();

        $table->string('penulis_3')->nullable();

        $table->string('nama_pena')->nullable();

        $table->string('kategori')->nullable();

        $table->enum('status', [
            'draft',
            'editing',
            'layout',
            'acc_penulis',
            'audit_isbn',
            'siap_isbn',
            'selesai'
        ])->default('draft');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
