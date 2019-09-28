<?php
namespace App\Services;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use ZuggrCloud\ZuggrCloud;

class Senders
{
    protected $cloud;

    public function __construct(ZuggrCloud $cloud)
    {
        $this->cloud = $cloud;
    }

    public function sendEmailValidation(
        string $lang,
        string $email,
        DateTime $expireAt,
        array $cache,
        array $titleData = [],
        array $bodyData = []
    ): void {
        $this->cloud->post('api/v1/send/validation', [
            'lang' => $lang,
            'email' => $email,
            'template_id' => \config('constant.zuggr_cloud.send.email.template.validation'),
            'host_password' => \config('constant.zuggr_cloud.send.email.host_password'),
            'expire_at' => $expireAt->format('Y-m-d H:i:s'),
            'cache' => $cache,
            'title_data' => $titleData,
            'body_data' => $bodyData
        ]);
    }

    public function sendEmail(
        string $lang,
        string $email,
        string $template,
        array $titleData = [],
        array $bodyData = []
    ): void {
        $this->cloud->post('api/v1/send/email', [
            'lang' => $lang,
            'email' => $email,
            'template_id' => \config('constant.zuggr_cloud.send.email.template.'.$template),
            'host_password' => \config('constant.zuggr_cloud.send.email.host_password'),
            'title_data' => $titleData,
            'body_data' => $bodyData
        ]);
    }
}
