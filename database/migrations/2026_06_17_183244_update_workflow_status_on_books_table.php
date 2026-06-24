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
    DB::statement("
        ALTER TABLE books
        MODIFY workflow_status
        ENUM(
            'draft',
            'editing',
            'layout',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai'
        )
        DEFAULT 'draft'
    ");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
