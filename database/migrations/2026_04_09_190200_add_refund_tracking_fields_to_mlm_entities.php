<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mlm_subscriptions', function (Blueprint $table): void {
            $table->timestamp('refund_requested_at')->nullable()->after('expires_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->text('refund_note')->nullable()->after('refunded_by');
        });

        Schema::table('mlm_orders', function (Blueprint $table): void {
            $table->timestamp('refund_requested_at')->nullable()->after('notes');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->text('refund_note')->nullable()->after('refunded_by');
        });

        Schema::table('mlm_invoices', function (Blueprint $table): void {
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->text('refund_note')->nullable()->after('refunded_at');
        });

        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->foreignId('refund_request_id')->nullable()->after('deposit_request_id')->constrained('mlm_refund_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('refund_request_id');
        });

        Schema::table('mlm_invoices', function (Blueprint $table): void {
            $table->dropColumn(['refunded_at', 'refund_note']);
        });

        Schema::table('mlm_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropColumn(['refund_requested_at', 'refunded_at', 'refund_note']);
        });

        Schema::table('mlm_subscriptions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropColumn(['refund_requested_at', 'refunded_at', 'refund_note']);
        });
    }
};
