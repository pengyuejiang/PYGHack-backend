<?php
namespace App\Console\Commands;

use Exception;
use App\Helpers;
use ZuggrCloud\ZuggrCloud;
use Illuminate\Console\Command;

/**
 * Class CloudInit
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class RegisterEmailConfig extends Command
{
    protected $cloud;

    public function __construct(ZuggrCloud $cloud)
    {
        $this->cloud = $cloud;
        parent::__construct();
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "register:email-config";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Register Email Config";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = config('constant.email_config');
        dump($this->cloud->put('app/send/email/config', $config));
    }
}
