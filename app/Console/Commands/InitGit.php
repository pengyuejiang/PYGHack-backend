<?php
namespace App\Console\Commands;

use Exception;
use App\Helpers;
use App\Models\Admins\Admin;
use Illuminate\Console\Command;

/**
 * Class CloudInit
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class InitGit extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "init:git {to}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Initialize Remote Git Repository";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $from = base_path().'/';
        $to = $this->argument('to');
        \exec("rsync -r {$from} {$to} --filter=':- .gitignore' --exclude=.git");
        \exec("cd {$to} && composer install");
    }
}
