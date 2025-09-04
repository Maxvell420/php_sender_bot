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
            'tg_jobs',
            function (Blueprint $table) {
                $table->id();
                $table->integer('actor_id');
                $table->integer('job_type');
                $table->json('json')->nullable();
                $table->enum('completed', ['yes', 'no'])->default('no');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tg_jobs');
    }
};
