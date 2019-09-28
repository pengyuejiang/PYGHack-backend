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

    /**
     * phpcs:disable
     * @OA\Post(
     *      path="passport/login",
     *      tags={"passport"},
     *      description="create new email template [scope:app]",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="email",type="string",example="xiaozimo@zuggr.com",description=""),
     *              @OA\Property(property="password",type="string",example="YourPass",description=""),
     *              @OA\Property(property="credentials",type="json",example={"username": "foo"},description=""),
     *          ),
     *      ),
     *      @OA\Response(response="200",description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="token_type",type="string",example="Bearer"),
     *              @OA\Property(property="access_token",type="string",example="eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
     *              @OA\Property(property="expires_in",type="number",example=86400),
     *          ),
     *      )
     * )
     * phpcs:enable
     */
    public function login(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'email' => 'email',
            'password' => ['required', 'string', 'min:6'],
            'credentials' => 'array'
        ]);

        if (isset($content['credentials'])) {
            $this->validator($content['credentials'], [
                //
            ]);

            $content['credentials'] = Helpers::only($content['data'], [
                //
            ]);
        }

        try {
            $oauth = $this->cloud->post(
                'resource/passport/oauth/login',
                Helpers::only($content, ['email', 'password', 'credentials'])
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
            'credentials' => 'required|array',
            'data' => 'required|array',
            'code' => 'required|string'
        ]);

        $this->validator($content['credentials'], [
            //
        ]);

        $this->validator($content['data'], [
            //
        ]);

        $content['data'] = Helpers::only($content['data'], [
            //
        ]);
        
        $content['credentials'] = Helpers::only($content['data'], [
            //
        ]);

        return $this->cloud->post(
            'resource/passport/oauth/register',
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
            'resource/passport/'.$id.'/forget',
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
            'credentials' => 'array',
        ]);

        $user = $this->cloud->get('resource/passport', array_merge(
            Helpers::only($content, ['email', 'data', 'credentials']),
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
            'credentials' => 'array',
            'page' => 'required|integer',
            'per-page' => 'integer',
            'order' => 'string',
            'by' => 'string|in:desc,asc'
        ]);

        return $this->cloud->get('resource/passport', Helpers::only([
            'email', 'data', 'credentials', 'page', 'per-page', 'order', 'by'
        ]));
    }

    public function deleteBatch(Request $request)
    {
        $content = $this->getContent($request);

        $this->idsValidator($content, 'users');

        $this->cloud->delete('resource/passport', $content);
    }

    public function delete(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->idsValidator($content, 'users');

        $this->cloud->delete(
            'resource/passport/'.$id,
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
            //
        ]);

        // some default values
        $content[$id]['data']['foo'] = 'bar';
        // some cannot be changed
        $content['data']['foo'] = $request->user()->data['foo'];

        return $this->cloud->put('resource/passport/'.$id, array_merge(
            ['token' => $request->user()->token],
            Helpers::only($content, ['data'])
        ));
    }

    public function putBatch(Request $request)
    {
        $content = $this->getContent($request);

        $this->batchValidator($content, 'users', [
            'data' => 'array'
        ]);

        foreach ($content['users'] as $id => $user) {
            $this->validator($user['data'], [
                //
            ]);

            $this->validator($user['credentials'], [
                //
            ]);

            // some default values
            $content[$id]['data']['foo'] = 'bar';
            // some cannot be changed
            $content['data']['foo'] = $request->user()->data['foo'];
        }

        return $this->cloud->put('resource/passport', $content);
    }

    public function putCredentials(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'credentials' => 'array',
            'email' => 'email',
            'password' => ['string', 'min:6'],
            'code' => 'required|string'
        ]);

        if (isset($content['credentials'])) {
            $this->validator($content['credentials'], [
                //
            ]);
        }

        // some default values
        $content['credentials']['foo'] = 'bar';

        return $this->cloud->put('resource/passport/'.$id.'/credentials', array_merge(
            ['token' => $request->user()->token],
            Helpers::only($content, ['credentials', 'email', 'password', 'code'])
        ), false);
    }
}
