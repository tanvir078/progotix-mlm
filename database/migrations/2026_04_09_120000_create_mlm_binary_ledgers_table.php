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
        Schema::create('mlm_binary_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('left_volume', 12, 2)->default(0);
            $table->decimal('right_volume', 12, 2)->default(0);
            $table->decimal('left_carry', 12, 2)->default(0);
            $table->decimal('right_carry', 12, 2)->default(0);
            $table->decimal('pair_volume', 12, 2)->default(0);
            $table->decimal('bonus_rate', 8, 4)->default(0.1000);
            $table->decimal('total_binary_bonus', 12, 2)->default(0);
            $table->timestamp('last_paired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mlm_binary_ledgers');
    }
};
