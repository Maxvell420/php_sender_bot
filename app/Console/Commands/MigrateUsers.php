<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateUsers extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate users from file to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = 'users.txt';
        if (!file_exists($path)) {
            $this->error('File not found');
            return;
        }
        $file = fopen($path, "r");
        while (($line = fgets($file)) !== false) {
            $user_data = json_decode($line, true);
            User::create($user_data);
        }

        fclose($file);
        // $Post = Post::where('id', '=', 1)->first();
        // $Post->content = ">Приветствую 🫶🏻 на Бесплатной информационной площадке  с пользой для здоровья\\.\n\nСкачай свой гайд \"7 главных анализов ЖКТ\" пользуйся будь здоров 🧑‍⚕️ ||✔️ Появились вопросы задай  в чате на канале ||👇\n\n📍 *ВНИМАНИЕ*: Только на этой площадке ценная информация\\. \n*Закрепи БОТ* чтобы не пропустить смс о новом гайде\\.\n\n📍 *Сохрани  этот Бот*: Только тут анонсы стартов \"Чистка\", чек\\-листы контроль здоровья, мини гайды\\.\n\n📍Без спама только самая суть с заботой о Вас\n\nП*оделись Ботом с тем кто тебе дорог 🫶🏻*";
        // $Post->save();
        // dd($Post->content);
    }
}
