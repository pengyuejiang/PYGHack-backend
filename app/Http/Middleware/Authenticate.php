<?php
namespace App\Http\Middleware;

use Closure;
use App\Helpers;
use App\Helpers\ErrorHelpers;
use ZuggrCloud\ZuggrCloud;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \ZuggrCloud\ZuggrCloud
     */
    protected $cloud;

    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory $cloud
     * @param  \ZuggrCloud\ZuggrCloud  $cloud
     * @return void
     */
    public function __construct(Auth $auth, ZuggrCloud $cloud)
    {
        $this->auth = $auth;
        $this->cloud = $cloud;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $this->getToken($request);

        if (!($userID = Helpers::getCache('passport', 'uid', $token))) {
            try {
                $user = $this->cloud->get('api/v1/passport/oauth/info', ['token' => $token]);
            } catch (\Exception $e) {
                app(ErrorHelpers::class)->throw(1001, $e);
            }

            Helpers::putCache(
                'passport',
                'uid',
                $user['request_oauth']['access_token'],
                $user['request_oauth']['expires_in'] - 60,
                $user['id']
            );
        } else {
            $user = $this->cloud->get('api/v1/passport/'.$userID, ['token' => $token]);
        }

        $user['token'] = $token;

        $request->user()->fill($user);

        return $next($request);
    }

    private function getToken($request)
    {
        if (!($token = $request->session()->get('token'))) {
            $content = app(Controller::class)->getContent($request);
            $token = isset($content['token']) ? $content['token'] : trim(
                str_replace('Bearer', '', $request->header('Authorization'))
            );
        }
        return $token;
    }
}
