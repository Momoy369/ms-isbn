<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ktp_number', 32)->nullable()->unique()->after('email');
            $table->string('ktp_name')->nullable()->after('ktp_number');
            $table->string('phone', 32)->nullable()->after('ktp_name');
            $table->text('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->boolean('is_profile_complete')->default(false)->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ktp_number',
                'ktp_name',
                'phone',
                'address',
                'birth_date',
                'is_profile_complete',
            ]);
        });
    }
};
