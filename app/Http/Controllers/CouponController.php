<?php
namespace App\Http\Controllers;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;
use ZuggrCloud\ZuggrCloud;

class CouponController extends Controller
{
    protected $cloud;

    public function __construct(ZuggrCloud $cloud)
    {
        $this->cloud = $cloud;
    }

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

    public function view(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] == config('constant')['user_types']['user']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        $out = app(Coupon::class)->view($id);

        if ($out['sponsor_id'] != $user->id && $user->data['type'] == config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return $out;
    }

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

        if ($user->data['type'] == config('constant')['user_types']['user']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        if ($user->data['type'] == config('constant')['user_types']['sponsor']) {
            $this->validator($content, [
                'sponsor_id' => 'required|array'
            ]);
            if (!in_array($user->id, $content['sponsor_id'])) {
                app(ErrorHelpers::class)->throw(1004);
            } elseif (count($content['sponsor_id']) > 1) {
                app(ErrorHelpers::class)->throw(1004);
            }
        }

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
            'consumed_meal_name'=>'string'
        ]);

        $user = $request->user();

        if ($user->data['type'] == config('constant')['user_types']['user']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        $out = app(Coupon::class)->view($id);

        if ($out['sponsor_id'] != $user->id && $user->data['type'] == config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->put($id, Helpers::only($content, [
            'is_consumed', 'consumed_by','consumed_meal_name'
        ]));
    }

    public function consume(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['user']) {
            app(ErrorHelpers::class)->throw(1004);
        };
        
        list($coupons, $count) = app(Coupon::class)->index(
            [
                'sponsor_id' => $id,
                'is_consumed'=>false
            ],
            1,
            1,
            'created_at',
            'asc'
        );
        
        if (count($coupons) == 0) {
            app(ErrorHelpers::class)->throw(3001);
        };
        
        return app(Coupon::class)->put(
            $coupon[0]['id'],
            [
                'is_consumed'=>true,
                'consumed_by'=>$user->id,
                'consumed_at'=>Carbon::now()
            ]
        );
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] == config('constant')['user_types']['user']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        $out = app(Coupon::class)->view($id);

        if ($out['sponsor_id'] != $user->id && $user->data['type'] == config('constant')['user_types']['sponsor']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(Coupon::class)->delete($id);
    }
}
