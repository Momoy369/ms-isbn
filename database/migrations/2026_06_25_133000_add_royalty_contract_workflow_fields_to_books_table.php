<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('royalty_contract_status')->default('pending_admin')->after('royalty_enabled_by_user_id');
            $table->unsignedInteger('royalty_contract_version')->default(1)->after('royalty_contract_status');
            $table->timestamp('royalty_contract_sent_at')->nullable()->after('royalty_contract_version');
            $table->timestamp('royalty_contract_accepted_at')->nullable()->after('royalty_contract_sent_at');
            $table->timestamp('royalty_contract_rejected_at')->nullable()->after('royalty_contract_accepted_at');
            $table->text('royalty_contract_acknowledgement')->nullable()->after('royalty_contract_rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'royalty_contract_status',
                'royalty_contract_version',
                'royalty_contract_sent_at',
                'royalty_contract_accepted_at',
                'royalty_contract_rejected_at',
                'royalty_contract_acknowledgement',
            ]);
        });
    }
};
