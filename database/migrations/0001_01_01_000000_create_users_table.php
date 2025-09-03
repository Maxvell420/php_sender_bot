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
            'users',
            function (Blueprint $table) {
                $table->id();
                $table->integer('tg_id', unsigned:true);
                $table->string('user_name');
                $table->timestamps();
            }
        );

        Schema::create(
            'posts',
            function (Blueprint $table) {
                $table->id();
                $table->text('content');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
    }
};
