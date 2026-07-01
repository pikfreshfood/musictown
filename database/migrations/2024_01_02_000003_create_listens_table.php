<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->date('listened_date');
            $table->timestamps();

            $table->unique(['user_id', 'song_id', 'listened_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listens');
    }
};
