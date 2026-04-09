<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlm_rank_achievements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rank_id')->constrained('mlm_ranks')->cascadeOnDelete();
            $table->decimal('bonus_amount', 12, 2)->default(0);
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'rank_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlm_rank_achievements');
    }
};
