<?php

use App\Models\Post;
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

        $post = new Post();
        $post->content = "Добрый день! 🫶🏻 Спасибо, что проявили интерес! Как и обещала, держите ваш гайд: «7 главных анализов ЖКТ». Надеюсь, он будет вам полезен! 

А теперь — маленький секрет: весь самый ценный контент я буду присылать именно сюда! 🤍

Почему этот бот удалять не стоит:
🧘🏼‍♀️Только тут — анонсы стартов чисток, дополнительные чек-листы и мини-гайды!

Обещаю не спамить, только самое важное! 🤍";
        $post->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
    }
};
