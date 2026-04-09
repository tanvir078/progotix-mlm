<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mlm_invoices', function (Blueprint $table): void {
            $table->foreignId('order_id')
                ->nullable()
                ->after('subscription_id')
                ->constrained('mlm_orders')
                ->nullOnDelete();

            $table->index(['order_id', 'status']);
        });

        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->foreignId('order_id')
                ->nullable()
                ->after('subscription_id')
                ->constrained('mlm_orders')
                ->nullOnDelete();

            $table->index(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->dropIndex(['order_id', 'type']);
            $table->dropConstrainedForeignId('order_id');
        });

        Schema::table('mlm_invoices', function (Blueprint $table): void {
            $table->dropIndex(['order_id', 'status']);
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
