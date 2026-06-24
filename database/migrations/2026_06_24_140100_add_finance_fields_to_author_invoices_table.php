<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('author_invoices', function (Blueprint $table) {
            $table->boolean('is_package_billing')->default(false)->after('type');
            $table->unsignedTinyInteger('installment_number')->nullable()->after('is_package_billing');
            $table->string('payment_method', 32)->nullable()->after('payment_proof');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->foreignId('verified_by_user_id')->nullable()->after('payment_reference')->constrained('users')->nullOnDelete();

            $table->index(['book_id', 'is_package_billing']);
            $table->index(['book_id', 'installment_number']);
        });
    }

    public function down(): void
    {
        Schema::table('author_invoices', function (Blueprint $table) {
            $table->dropIndex(['book_id', 'is_package_billing']);
            $table->dropIndex(['book_id', 'installment_number']);
            $table->dropConstrainedForeignId('verified_by_user_id');
            $table->dropColumn([
                'is_package_billing',
                'installment_number',
                'payment_method',
                'payment_reference',
            ]);
        });
    }
};
