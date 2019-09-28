<?php
namespace App\Http\Controllers;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
    * phpcs:disable
    * @OA\Post(
    *      path="coupon/register",
    *      tags={"passport"},
    *      description="create new email template [scope:app]",
    *      @OA\RequestBody(
    *          @OA\JsonContent(
    *              @OA\Property(property="sponsor_id",type="integer",example="123",description=""),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="sponsor_id",type="integer",example="123"),
    *              @OA\Property(property="is_consumed",type="boolean",example="false"),
    *              @OA\Property(property="consumed_by",type="integer",example="123"),
    *              @OA\Property(property="consumed_at",type="timestamp",example="2019-09-28 02:04:48.870742 UTC (+00:00)"),
    *              @OA\Property(property="consumed_meal_name",type="string",example="BIGMAC"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */

    public function register(Request $request)
    {
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->add([
            'is_consumed' => false,
            'sponsor_id' => $user->id,
            'consumed_by'=> null,
            'consumed_at'=> null,
            'consumed_meal_name'=>null
        ]);
    }

    /**
    * phpcs:disable
    * @OA\Post(
    *      path="coupon/view",
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
    *              @OA\Property(property="sponsor_id",type="integer",example="123",description=""),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="sponsor_id",type="integer",example="123"),
    *              @OA\Property(property="is_consumed",type="boolean",example="false"),
    *              @OA\Property(property="consumed_by",type="integer",example="123"),
    *              @OA\Property(property="consumed_at",type="timestamp",example="2019-09-28 02:04:48.870742 UTC (+00:00)"),
    *              @OA\Property(property="consumed_meal_name",type="string",example="BIGMAC"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */


    public function view(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->view($id);
    }

    /**
    * phpcs:disable
    * @OA\Post(
    *      path="coupon/index",
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
    *               @OA\Property(property="sponsor_id",type="array",example="[123,124]",description=""),
    *               @OA\Property(property="consumed_by",type="array",example="[123,124]",description=""),
    *          ),
    *      ),
    *      @OA\Response(response="200",description="success",
    *          @OA\JsonContent(
    *              @OA\Property(property="count",type="integer",example="123"),
    *              @OA\Property(property="coupon",type="json",example="{...}"),
    *          ),
    *      )
    * )
    * phpcs:enable
    */

    public function index(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'page' => 'required|integer',
            'per-page' => 'integer',
            'order' => 'string',
            'by' => 'string|in:desc,asc',
            'sponsor_id' => 'array',
            'consumed_by' => 'array'
        ]);

        list($page, $perPage, $order, $by) = $this->getIndexAttributes($content);

        list($coupons, $count) = app(Coupon::class)->index(
            Helpers::only($content, ['sponsor_id', 'consumed_by']),
            $page,
            $perPage,
            $order,
            $by
        );

        return [
            'coupons' => $coupons,
            'count' => $count
        ];
    }

    public function put(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'is_consumed' => 'required|boolean',
            'consumed_by' => 'required|integer',
            'consumed_at' => 'timestamp',
            'consumed_meal_name'=>'string'
        ]);
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->put($id, Helpers::only($content, [
            'is_consumed', 'consumed_by','consumed_meal_name'
        ]));
    }

    public function consume(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'is_consumed' => 'required|boolean',
            'consumed_by' => 'required|integer',
            'consumed_at' => 'timestamp',
            'consumed_meal_name'=>'string'
        ]);

        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->put($id, array_merge(Helpers::only($content, [
            'is_consumed', 'consumed_by','consumed_meal_name'
        ]), [
            'consumed_at'=>Carbon::now()
        ]));
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->delete($id);
    }
}
