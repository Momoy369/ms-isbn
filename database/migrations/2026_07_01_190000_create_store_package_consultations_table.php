<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_package_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('publishing_package_id')->constrained('publishing_packages')->cascadeOnDelete();

            $table->string('package_name', 160);
            $table->decimal('package_base_price', 15, 2)->default(0);

            $table->string('customer_name', 120);
            $table->string('customer_phone', 32);
            $table->string('customer_email', 120)->nullable();

            $table->string('manuscript_title', 190)->nullable();
            $table->string('manuscript_genre', 120)->nullable();
            $table->unsignedInteger('estimated_page_count')->nullable();
            $table->date('target_publish_date')->nullable();
            $table->string('budget_range', 64)->nullable();

            $table->json('selected_services')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->text('notes')->nullable();

            $table->string('status', 32)->default('pending');
            $table->string('source', 64)->default('storefront');

            $table->timestamps();

            $table->index(['status', 'created_at'], 'spc_status_created_idx');
            $table->index(['publishing_package_id', 'created_at'], 'spc_package_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_package_consultations');
    }
};
