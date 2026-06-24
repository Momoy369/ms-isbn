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
        Schema::table('books', function (Blueprint $table) {

            $table->date('tanggal_acc_penulis')
                ->nullable()
                ->after('workflow_status');

            $table->date('tanggal_pengajuan_isbn')
                ->nullable()
                ->after('tanggal_acc_penulis');

            $table->date('tanggal_isbn_terbit')
                ->nullable()
                ->after('tanggal_pengajuan_isbn');

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
