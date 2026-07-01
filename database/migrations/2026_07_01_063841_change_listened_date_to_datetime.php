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
        Schema::table('listens', function (Blueprint $table) {
            $table->dateTime('listened_at')->nullable();
        });

        DB::statement('UPDATE listens SET listened_at = listened_date');

        Schema::table('listens', function (Blueprint $table) {
            $table->dropUnique('listens_user_id_song_id_listened_date_unique');
            $table->dropColumn('listened_date');
            $table->index(['user_id', 'song_id', 'listened_at']);
        });
    }

    public function down(): void
    {
        Schema::table('listens', function (Blueprint $table) {
            $table->date('listened_date')->nullable();
        });

        DB::statement('UPDATE listens SET listened_date = listened_at');

        Schema::table('listens', function (Blueprint $table) {
            $table->unique(['user_id', 'song_id', 'listened_date']);
            $table->dropIndex(['user_id', 'song_id', 'listened_at']);
            $table->dropColumn('listened_at');
        });
    }
};
