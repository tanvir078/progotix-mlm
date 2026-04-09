<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlm_ranks', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('badge_color')->default('zinc');
            $table->unsignedInteger('direct_referrals_required')->default(0);
            $table->decimal('personal_sales_required', 12, 2)->default(0);
            $table->decimal('team_volume_required', 12, 2)->default(0);
            $table->decimal('bonus_amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlm_ranks');
    }
};
