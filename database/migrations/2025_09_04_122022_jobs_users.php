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
        Schema::Create(
            'jobs_users',
            function (Blueprint $table) {
                $table->id();
                $table->integer('job_id');
                $table->integer('actor_id');
                $table->enum('completed', ['yes', 'no'])->default('no');
            }

        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs_users');
    }
};
