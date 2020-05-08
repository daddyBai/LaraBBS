<?php

namespace App\Http\Middleware;

use Closure;

class EnsureEmailIsVerified
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
        /*
         * 1. 如果用户已经登录
         * 2. 并且还未认证 Email
         * 3. 并且访问的不是 email 验证相关 URL 或者退出 URL
         */

        if($request->user()
            && ! $request->user()->hasVerifiedEmail()
            && ! $request->is('email/*', 'logout')){

            // 根据客户端返回相对应的内容
            return $request->expectsJson()
                ? abort(403, '你的邮箱未认证')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
