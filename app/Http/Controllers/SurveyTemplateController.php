<?php
namespace App\Http\Controllers;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use Illuminate\Http\Request;
use App\Models\SurveyTemplate;
use Carbon\Carbon;

class SurveyTemplateController extends Controller
{
    /**
    * phpcs:disable
    * @OA\Post(
    *      path="survey/template/register",
    *      tags={"passport"},
    *      description="create new email template [scope:app]",
    *      @OA\RequestBody(
    *          @OA\JsonContent(
    *              @OA\Property(property="user_id",type="integer",example="123",description=""),
    *              @OA\Property(property="body",type="json",example="{...}",description=""),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="user_id",type="integer",example="123"),
    *              @OA\Property(property="body",type="json",example="{...}"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */

    public function register(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'body' => 'array'
        ]);

        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['authority']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(SurveyTemplate::class)->add([
            'owner' => $user->id,
            'body'=> Helpers::only($content, ['body'])
        ]);
    }

    /**
    * phpcs:disable
    * @OA\Post(
    *      path="survey/template/view",
    *      tags={"passport"},
    *      description="create new email template [scope:app]",
    *      @OA\Parameter(
    *          name="id",
    *          in="path",
    *          required=true,
    *          @OA\Schema(
    *              type="integer"
    *          ),
    *          example="123"
    *      ),
    *      @OA\RequestBody(
    *          @OA\JsonContent(
    *               @OA\Property(property="user_id",type="integer",example="123",description=""),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *                @OA\Property(property="user_id",type="integer",example="123"),
    *                @OA\Property(property="body",type="json",example="{...}"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */

    public function view(Request $request, $id)
    {
        return app(SurveyTemplate::class)->view($id);
    }

    /**
    * phpcs:disable
    * @OA\Post(
    *      path="survey/template/index",
    *      tags={"passport"},
    *      description="create new email template [scope:app]",
    *      @OA\Parameter(
    *          name="id",
    *          in="path",
    *          required=true,
    *          @OA\Schema(
    *              type="integer"
    *          ),
    *          example="123"
    *      ),
    *      @OA\RequestBody(
    *          @OA\JsonContent(
    *               @OA\Property(property="page",type="integer",example="123",description=""),
    *               @OA\Property(property="per-page",type="integer",example="123",description=""),
    *               @OA\Property(property="order",type="string",example="name",description=""),
    *               @OA\Property(property="by",type="string",example="desc",description=""),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="body",type="json",example="{}"),
    *              @OA\Property(property="count",type="integer",example="123"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */

    public function index(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'page' => 'required|integer',
            'per-page' => 'integer',
            'order' => 'string',
            'by' => 'string|in:desc,asc',
            'owner_id' => 'array'
        ]);

        list($page, $perPage, $order, $by) = $this->getIndexAttributes($content);

        list($templates, $count) = app(SurveyTemplate::class)->index(
            Helpers::only($content, ['owner_id']),
            $page,
            $perPage,
            $order,
            $by
        );

        return [
            'survey_templates' => $templates,
            'count' => $count
        ];
    }
    /**
    * phpcs:disable
    * @OA\Post(
    *      path="survey/template/put",
    *      tags={"passport"},
    *      description="create new email template [scope:app]",
    *      @OA\Parameter(
    *          name="id",
    *          in="path",
    *          required=true,
    *          @OA\Schema(
    *              type="integer"
    *          ),
    *          example="123"
    *      ),
    *      @OA\RequestBody(
    *          @OA\JsonContent(
    *               @OA\Property(property="user_id",type="integer",example="123",description=""),
    *               @OA\Property(property="body",type="json",example="{}"),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="user_id",type="integer",example="123"),
    *              @OA\Property(property="body",type="json",example="{...}"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */

    public function put(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'body'=>'json'
        ]);

        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['authority']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(SurveyTemplate::class)->put($id, Helpers::only($content, ['body']));
    }

    /**
    * phpcs:disable
    * @OA\Post(
    *      path="survey/template/delete",
    *      tags={"passport"},
    *      description="create new email template [scope:app]",
    *      @OA\Parameter(
    *          name="id",
    *          in="path",
    *          required=true,
    *          @OA\Schema(
    *              type="integer"
    *          ),
    *          example="123"
    *      ),
    *      @OA\RequestBody(
    *          @OA\JsonContent(
    *              @OA\Property(property="user_d",type="integer",example="123"),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="user_id",type="integer",example="123"),
    *              @OA\Property(property="body",type="json",example="{...}"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */


    public function delete(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['authority']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(SurveyTemplate::class)->del($id);
    }
}
