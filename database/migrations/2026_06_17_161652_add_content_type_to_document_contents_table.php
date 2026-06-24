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
        Schema::table(
            'document_contents',
            function (Blueprint $table) {

                $table->string('content_type')
                    ->nullable()
                    ->after('book_file_id');

            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_contents', function (Blueprint $table) {
            //
        });
    }
};
