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
class InitApp extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "init:app";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Initialize Zuggr Application";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("\n🔵   migrating database...\n");
        $this->call('migrate');

        $this->line("\n🔵   granting permissions to necessary folders...\n");
        Helpers::chmod(\storage_path(), 0777, 0777);
        Helpers::chmod(\public_path(), 0777, 0777);

        $this->line("\n🔵   generating doc files...\n");
        $this->call('swagger-lume:generate');
    }
}
