<?php
namespace App\Http\Controllers;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use ZuggrCloud\ZuggrCloud;
use Illuminate\Http\Request;

class PassportController extends Controller
{
    protected $cloud;

    public function __construct(ZuggrCloud $cloud)
    {
        $this->cloud = $cloud;
    }

    public function login(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => 'email',
            'password' => ['required', 'string', 'min:6']
        ]);

        try {
            $oauth = $this->cloud->post(
                'api/v1/passport/oauth/login',
                Helpers::only($content, ['email', 'password'])
            );
        } catch (\Exception $e) {
            app(ErrorHelpers::class)->throw(1003, $e);
        }

        $request->session()->put('token', $oauth['access_token']);

        return $oauth;
    }

    public function register(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => 'email',
            'password' => ['required', 'string', 'min:6'],
            'data' => 'required|array',
            'code' => 'required|string'
        ]);

        $this->validator($content['data'], [
            'name' => 'required|string',
            'type' => 'required|integer',
            'meal_name' => 'string'
        ], [
            'meal_name' => null
        ]);

        $content['data'] = Helpers::only($content['data'], [
            'name', 'type', 'meal_name'
        ]);
        
        $content['credentials'] = [];

        return $this->cloud->post(
            'api/v1/passport/oauth/register',
            Helpers::only($content, ['email', 'password', 'credentials', 'data', 'code'])
        );
    }

    public function info(Request $request)
    {
        return $request->user();
    }

    public function forget(Request $request, $id)
    {
        $this->cloud->delete(
            'api/v1/passport/'.$id.'/forget',
            ['token' => $request->user()->token]
        );

        Helpers::deleteCache(
            'passport',
            'uid',
            $request->user()->token
        );
    }

    public function ping(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => 'email',
            'data' => 'array',
        ]);

        $user = $this->cloud->get('api/v1/passport', array_merge(
            Helpers::only($content, ['email', 'data']),
            [
                'per-page' => 1,
                'page' => 1
            ]
        ));

        return [
            'pong' => count($user['users']) == 1
        ];
    }

    public function index(Request $request)
    {
        // index API should not be exposed for security reasons
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => 'email',
            'data' => 'array',
            'page' => 'required|integer',
            'per-page' => 'integer',
            'order' => 'string',
            'by' => 'string|in:desc,asc'
        ]);

        return $this->cloud->get('api/v1/passport', Helpers::only([
            'email', 'data', 'page', 'per-page', 'order', 'by'
        ]));
    }

    public function delete(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->idsValidator($content, 'users');

        $this->cloud->delete(
            'api/v1/passport/'.$id,
            ['token' => $request->user()->token]
        );
    }

    public function put(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'data' => 'required|array'
        ]);

        $this->validator($content['data'], [
            'name' => 'required|string',
            'meal_name' => 'string'
        ]);

        $content['data'] = Helpers::only($content['data'], [
            'name', 'type', 'meal_name'
        ]);

        return $this->cloud->put('api/v1/passport/'.$id, array_merge(
            ['token' => $request->user()->token],
            Helpers::only($content, ['data'])
        ));
    }

    public function putCredentials(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => 'email',
            'password' => ['string', 'min:6'],
            'code' => 'required|string'
        ]);

        return $this->cloud->put('api/v1/passport/'.$id.'/credentials', array_merge(
            ['token' => $request->user()->token],
            Helpers::only($content, ['email', 'password', 'code'])
        ), false);
    }
}
