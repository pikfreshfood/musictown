<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->decimal('ncoin_amount', 10, 2)->nullable()->after('pin');
        });
    }

    public function down(): void
    {
        Schema::table('payment_accounts', function (Blueprint $table) {
            $table->dropColumn('ncoin_amount');
        });
    }
};
