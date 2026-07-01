<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paystack_virtual_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wallet_funding_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paystack_reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('channel')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_bank')->nullable();
            $table->string('sender_account_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
