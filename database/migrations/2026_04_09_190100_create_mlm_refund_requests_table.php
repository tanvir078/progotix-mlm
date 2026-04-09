<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlm_refund_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('mlm_subscriptions')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('mlm_orders')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('mlm_invoices')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30);
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 12, 2);
            $table->decimal('commission_reversal_amount', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlm_refund_requests');
    }
};
