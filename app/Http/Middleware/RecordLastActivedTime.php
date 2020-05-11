<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RecordLastActivedTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 这是后置中间件写法，$next 已经执行完毕并返回响应 $response，
        $response = $next($request);

        if(Auth::check()){
            Auth::user()->recordLastActivedAt();
        }

        return $response;
    }
}
