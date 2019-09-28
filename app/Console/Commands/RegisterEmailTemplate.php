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
class RegisterEmailTemplate extends Command
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
    protected $signature = "register:email-template";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Register Email Template";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $localTemplates = config('constant.email_templates');

        try {
            $templates = $this->cloud->get(
                'app/send/email/template',
                ['page' => 1, 'pre-page' => 9999999]
            );
        } catch (\Exception $e) {
            $response = $e->getResponse();
            dd((string)$response->getBody());
        }

        $templates = $templates['app_email_templates'];
        foreach ($templates as $template) {
            $this->cloud->delete('app/send/email/template/'.$template['id']);
        }

        $env = [];
        $out = [];
        foreach ($localTemplates as $envVal => $localTemplate) {
            $template = $this->cloud->post('app/send/email/template', $localTemplate);
            $env[$envVal] = "\"{$template['id']}\"";
            $out[] = $template;
        }
        Helpers::setEnvValue($env);
        dump($out);
    }
}
