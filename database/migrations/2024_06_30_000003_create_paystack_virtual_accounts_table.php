<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('customer_code')->nullable()->index();
            $table->unsignedBigInteger('dedicated_account_id')->nullable()->index();
            $table->string('bank_name')->nullable();
            $table->string('bank_slug')->nullable();
            $table->string('account_number')->unique();
            $table->string('account_name')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->boolean('active')->default(true);
            $table->boolean('assigned')->default(false);
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_virtual_accounts');
    }
};
