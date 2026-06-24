<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'editor',
                'layouter',
                'designer',
                'isbn',
                'author',
                'owner',
                'finance',
                'superadmin',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'editor',
                'layouter',
                'designer',
                'isbn',
                'author',
                'owner',
                'finance',
            ])->change();
        });
    }
};
