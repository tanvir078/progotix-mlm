<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlm_payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type', 30);
            $table->string('country_code', 2)->nullable();
            $table->string('currency_code', 8)->default('USD');
            $table->string('provider_name')->nullable();
            $table->string('destination_label')->nullable();
            $table->string('destination_value')->nullable();
            $table->text('instructions')->nullable();
            $table->decimal('min_amount', 12, 2)->default(0);
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->decimal('fixed_charge', 12, 2)->default(0);
            $table->decimal('percent_charge_rate', 8, 4)->default(0);
            $table->boolean('supports_deposit')->default(true);
            $table->boolean('supports_withdrawal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'type']);
            $table->index(['country_code', 'is_active']);
        });

        Schema::create('mlm_deposit_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('mlm_payment_methods')->nullOnDelete();
            $table->string('payment_method_name');
            $table->string('payment_method_type', 30);
            $table->json('payment_method_snapshot')->nullable();
            $table->string('currency', 8)->default('USD');
            $table->decimal('amount', 12, 2);
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'submitted_at']);
        });

        Schema::table('mlm_withdrawal_requests', function (Blueprint $table): void {
            $table->foreignId('payment_method_id')
                ->nullable()
                ->after('user_id')
                ->constrained('mlm_payment_methods')
                ->nullOnDelete();

            $table->index(['payment_method_id', 'status']);
        });

        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->foreignId('deposit_request_id')
                ->nullable()
                ->after('order_id')
                ->constrained('mlm_deposit_requests')
                ->nullOnDelete();

            $table->index(['deposit_request_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('mlm_transactions', function (Blueprint $table): void {
            $table->dropIndex(['deposit_request_id', 'type']);
            $table->dropConstrainedForeignId('deposit_request_id');
        });

        Schema::table('mlm_withdrawal_requests', function (Blueprint $table): void {
            $table->dropIndex(['payment_method_id', 'status']);
            $table->dropConstrainedForeignId('payment_method_id');
        });

        Schema::dropIfExists('mlm_deposit_requests');
        Schema::dropIfExists('mlm_payment_methods');
    }
};
