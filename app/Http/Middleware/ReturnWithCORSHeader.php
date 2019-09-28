<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers;

class ReturnWithCORSHeader
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', '*');
        $response->header(
            'Access-Control-Allow-Headers',
            'Origin, X-Requested-With,Authorization, Content-Type, Accept'
        );
        return $response;
    }
}
