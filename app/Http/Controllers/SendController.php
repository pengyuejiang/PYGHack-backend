<?php
namespace App\Http\Controllers;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use ZuggrCloud\ZuggrCloud;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SendController extends Controller
{
    protected $cloud;

    public function __construct(ZuggrCloud $cloud)
    {
        $this->cloud = $cloud;
    }

    public function sendValidation(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => ['required', 'email'],
            'cache' => ['array']
        ], [
            'cache' => []
        ]);

        try {
            $this->cloud->post(
                'api/v1/send/validation',
                array_merge(
                    Helpers::only($content, ['email', 'cache']),
                    [
                        'lang' => 'en',
                        'host_password' => \env('EMAIL_CONFIG_PASSWORD'),
                        'template_id' => \env('VALIDATION_CODE_EMAIL_TEMPLATE_ID'),
                        'expire_at' => Carbon::now()->addMinutes(20)->format('Y-m-d H:i:s')
                    ]
                )
            );
        } catch (\Exception $e) {
            app(ErrorHelpers::class)->throw(1003, $e);
        }
    }
}
