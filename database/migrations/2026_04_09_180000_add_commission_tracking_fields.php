<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->string('reference_key')->nullable()->after('deposit_request_id');
            $table->unsignedTinyInteger('commission_level')->nullable()->after('reference_key');
            $table->unique('reference_key');
        });

        Schema::table('mlm_orders', function (Blueprint $table): void {
            $table->unsignedInteger('commission_cycle')->default(0)->after('team_bonus_amount');
        });
    }

    public function down(): void
    {
        Schema::table('mlm_orders', function (Blueprint $table): void {
            $table->dropColumn('commission_cycle');
        });

        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->dropUnique(['reference_key']);
            $table->dropColumn(['reference_key', 'commission_level']);
        });
    }
};
