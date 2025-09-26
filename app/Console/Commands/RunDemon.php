<?php

namespace App\Console\Commands;

use App\Libs\Infra\Context;
use App\Telegram\TelegramSenderDemon;
use Illuminate\Console\Command;

class RunDemon extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-demon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        $context = new Context();
        $demon = new TelegramSenderDemon($context);
        $demon->run();
    }
}
