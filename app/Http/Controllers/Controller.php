<?php

/**
 * @OA\Info(title="Your awesome app", version="0.0.1")
 * @OA\Server(url="")
 * @OA\SecurityScheme(
 *   securityScheme="auth",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 */

namespace App\Http\Controllers;

use App\Helpers\ErrorHelpers;
use App\Helpers;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function validator(array &$content, array $rules, array $default = [])
    {
        $validator = Validator::make($content, $rules);

        foreach ($default as $k => $v) {
            $content[$k] = isset($content[$k]) ? $content[$k] : $v;
        }

        if ($validator->fails()) {
            app(ErrorHelpers::class)->throw(1002, json_encode($validator->errors()));
        }
    }

    public function batchValidator(array &$content, string $resource, array $rules)
    {
        if (!isset($content[$resource])) {
            app(ErrorHelpers::class)->throw(1002, 'input must have "'.$resource.'" object that contains batch');
        }

        foreach ($content[$resource] as $v) {
            $validator = Validator::make($v, $rules);

            if ($validator->fails()) {
                app(ErrorHelpers::class)->throw(1002, json_encode($validator->errors()));
            }
        }
    }

    public function idsValidator(array &$content, string $resource)
    {
        if (!isset($content[$resource])) {
            app(ErrorHelpers::class)->throw(1002, 'input must have "'.$resource.'" object that contains batch');
        }

        foreach ($content[$resource] as $v) {
            if (!is_int($v) and !is_string($v)) {
                app(ErrorHelpers::class)->throw(1002, 'input must be an array of integers or strings');
            }
        }
    }

    public function getContent(&$request)
    {
        $rawContent = $request->getContent();

        $a = Helpers::toArray(Helpers::isJson($rawContent) ? $rawContent : $request->all());

        $b = Helpers::toArray($request->query());

        $out = array_merge($a, $b);

        foreach ($out as $i => $o) {
            if (Helpers::isJson($o)) {
                $out[$i] = Helpers::toArray($o);
            }
        }

        return $out;
    }

    public function getIndexAttributes(array &$content)
    {
        $order = isset($content['order']) ? $content['order'] : 'created_at';
        return [
            isset($content['page']) ? $content['page'] : 1,
            isset($content['per-page']) ? $content['per-page'] : 35,
            $order,
            isset($content['by'])? $content['by'] : is_array($order) ? null : 'desc'
        ];
    }
}
