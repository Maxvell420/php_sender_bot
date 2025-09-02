<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create(
            'states',
            function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->foreignId('actor_id')->unique('user')->references('id')->on('users')->onDelete('cascade');
                $table->integer('state_id');
                $table->json('json')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('states');
    }
};
