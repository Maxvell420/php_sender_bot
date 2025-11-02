<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('tg_id'); // Простой индекс
        });

        Schema::table('tg_jobs', function (Blueprint $table) {
            $table->index('actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tg_id']); // Важно для отката миграции
        });


        Schema::table('tg_jobs', function (Blueprint $table) {
            $table->dropIndex(['actor_id']);
        });
    }
};
