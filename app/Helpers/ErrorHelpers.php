<?php
namespace App\Helpers;

use App\Helpers;
use Illuminate\Http\Response;
use Illuminate\Http\Exceptions\HttpResponseException;

class ErrorHelpers
{
    protected $error_codes;

    protected $report = [
        //
    ];

    public function __construct()
    {
        $this->error_codes = [
            // validation error
            1001 => [422, 'validation', 'invalid token'],
            1002 => [422, 'validation', 'invalid input'],
            1003 => [422, 'validation', 'invalid credentials'],
            1004 => [403, 'validation', 'not allowed to access'],

            // system error
            2001 => [500, 'system', 'internal error'],
            2002 => [500, 'system', 'Zuggr Cloud error'],

            // resource error
            3001 => [404, 'resource', 'resource not found'],
            3002 => [404, 'resource', 'route not found']
        ];
    }

    /**
     * Throw error
     *
     * @param integer $code
     * @param $exception
     * @param boolean $throwError
     * @return void
     */
    public function throw(int $code, $exception = null, bool $throwError = true)
    {
        if (method_exists($exception, 'getMessage')) {
            $errorMsg = $exception->getMessage();
        } else {
            $errorMsg = is_string($exception) ? $exception : null;
        }

        $errorMsg = stripslashes($errorMsg);

        $response = [
            'name' => $this->error_codes[$code][1],
            'code' => $code,
            'error_msg' => $this->error_codes[$code][2],
            'exception' => $errorMsg
        ];

        if (env('APP_ENV') == 'dev' || env('APP_ENV') == 'testing') {
            if (Helpers::isJson($response['exception'])) {
                $response['exception'] = Helpers::toArray($response['exception']);
            }

            if (method_exists($exception, 'getTraceAsString')) {
                $trace = $exception->getTraceAsString();
            } else {
                try {
                    throw new \Exception();
                } catch (\Exception $e) {
                    $trace = $e->getTraceAsString();
                }
            }
            $trace = stripslashes($trace);
            $trace = explode('#', $trace);
            $response = array_merge($response, ['trace' => $trace]);
        } else {
            $exception = explode('#', $response['exception'])[0];
        }

        if ($throwError) {
            throw new HttpResponseException(
                response()->json($response, $this->error_codes[$code][0])
            );
        } else {
            return response()->json($response, $this->error_codes[$code][0]);
        }
    }
}
