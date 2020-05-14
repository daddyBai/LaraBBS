<?php

namespace App\Http\Controllers\Api;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class VerificationCodesController extends Controller
{
    //

    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $captchaData = Cache::get($request->captcha_key);

        if(! $captchaData){
            abort(403, '图片验证码失效');
        }

        if(! hash_equals($captchaData['code'], $request->captcha_code)){
            Cache::forget($request->captcha_key);
            throw new AuthenticationException('验证码错误');
        }

        $phone = $captchaData['phone'];

        if(! app()->environment('production')){
            $code = '1234';
        }else{
            // 生成4位随机数，左侧补0
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            try{
            $result = $easySms->send($phone, [
                'template' => config('easysms.gateways.aliyun.templates.register'),
                'data' => [ 'code' => $code ]
            ]);
            }catch (NoGatewayAvailableException $exception){
                $message = $exception->getException('aliyun')->getMessage();
                abort(500, $message?:'短信发送异常');
            }
        }

        $key = 'verificationCode_' . Str::random(15);
        $expiredAt = now()->addMinutes(5);

        // 缓存验证码 5 分钟过期。
        Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        // 清除图片验证码缓存
        Cache::forget($request->captcha_key);

        return response()->json([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);
    }
}

# code = 0710y1SI0LkiDd2AlCSI0AgQRI00y1SG
# code = 071aeUfi2VzOPE0UYRei2ml8gi2aeUf1
# access_token = 33_0fVe0NXLKcBaexh0c0xU_u98SkQwudKiWdGx8rlaKvOUJkF92N2HVKF_K9PynRTKoiANk6uP-rcZFw5T6PlzKg
# access_token = 33_8Pr1Uy2ufxR7tg0KkFZv-DiDWPENaLQ9AZOw1Zlq8B3wFXffi-zeduYE-VhDQKVqkHY-c917y1VKkozq2lrL1w
# openid = ol53Lwo552-gzCUvE1dUI5x6hyUI
# openid = ol53Lwo552-gzCUvE1dUI5x6hyUI

# 获取个人信息 api  https://api.weixin.qq.com/sns/userinfo?access_token=
//
// 获取授权 code
//https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxfbae0bc486e6e4ec&redirect_uri=http://bbs.cc&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect
// 获取 access_token
//https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxfbae0bc486e6e4ec&secret=f52689343ecfd2efa16654a97d6fb85f&code=0710y1SI0LkiDd2AlCSI0AgQRI00y1SG&grant_type=authorization_code
//
//https://api.weixin.qq.com/sns/userinfo?access_token=33_jtBXLAqOeCjmvEzk9z_gQJFT1sRt5gHWmHIT8ckyYlbHptIrv5nTWyUVgTJAX9IaZk5-W9oxU6h_9eCwQi3B0g&openid=ol53Lwo552-gzCUvE1dUI5x6hyUI&lang=zh_CN
