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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('balance');
            $table->foreignId('binary_parent_id')->nullable()->after('referrer_id')->constrained('users')->nullOnDelete();
            $table->string('binary_position', 10)->nullable()->after('binary_parent_id');
            $table->index(['binary_parent_id', 'binary_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['binary_parent_id', 'binary_position']);
            $table->dropConstrainedForeignId('binary_parent_id');
            $table->dropColumn(['is_admin', 'binary_position']);
        });
    }
};
