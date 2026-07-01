<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ncoin_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->boolean('is_used')->default(false);
            $table->foreignId('used_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ncoin_codes');
    }
};
