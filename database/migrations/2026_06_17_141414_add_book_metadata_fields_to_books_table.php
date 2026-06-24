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
    Schema::table('books', function ($table) {

        $table->string('jumlah_halaman')
            ->nullable();

        $table->string('ukuran_buku')
            ->nullable();

        $table->string('cetakan')
            ->nullable();

        $table->string('designer')
            ->nullable();

        $table->year('tahun_copyright')
            ->nullable();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            //
        });
    }
};
