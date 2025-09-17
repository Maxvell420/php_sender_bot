<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::Create(
            'bot_channels',
            function (Blueprint $table) {
                $table->id();
                $table->string('channel_id');
                $table->string('channel_link')->nullable();
                $table->integer('status');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('bot_channels');
    }
};
