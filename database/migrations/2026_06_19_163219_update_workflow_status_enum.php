<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE books
            MODIFY workflow_status ENUM(
                'draft',
                'editing',
                'editing_review',
                'layout',
                'layout_review',
                'cover_design',
                'cover_review',
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

    public function down(): void
    {
        DB::statement("
            ALTER TABLE books
            MODIFY workflow_status ENUM(
                'draft',
                'editing',
                'layout',
                'cover_design',
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
};