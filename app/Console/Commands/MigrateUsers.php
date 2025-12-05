<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateUsers extends Command {

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
    public function handle() {
        $path = 'posts.txt';

        if( !file_exists($path) ) {
            $this->error('File not found');
            return;
        }

        $file = fopen($path, "r");

        while( ($line = fgets($file)) !== false ) {
            $user_data = json_decode($line, true);
            Post::create($user_data);
        }

        fclose($file);
    }
}
