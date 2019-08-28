<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * 登录
     *
     * @author AdamTyn
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Http\Response;
     */
    public function login(Request $request)
    {
        $response = array('code' => '0');

        try {
            $user = User::where('name', $request->input('name'))
                ->where('password', md5($request->input('password')))->first();
            if (!$user) {
                $response['code'] = '5001';
                $response['message'] = '账户名或者';
            } else if (!$token = Auth::login($user)) {
                $response['code'] = '5000';
                $response['message'] = '系统错误，无法生成令牌';
            } else {
                $response['data']['user_id'] = strval($user->id);
                $response['data']['access_token'] = $token;
                $response['data']['expires_in'] = strval(time() + 86400);
            }
        } catch (QueryException $queryException) {
            $response['code'] = '5002';
            $response['message'] = '无法响应请求，服务端异常';
        }

        return response()->json($response);
    }

    /**
     * 用户登出
     *
     * @author AdamTyn
     *
     * @return \Illuminate\Http\Response;
     */
    public function logout()
    {
        $response = array('code' => '0');

        Auth::invalidate(true);

        return response()->json($response);
    }

    /**
     * 更新用户Token
     *
     * @author AdamTyn
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Http\Response;
     */
    public function refreshToken()
    {
        $response = array('code' => '0');

        if (!$token = Auth::refresh(true, true)) {
            $response['code'] = '5000';
            $response['errorMsg'] = '系统错误，无法生成令牌';
        } else {
            $response['data']['access_token'] = $token;
            $response['data']['expires_in'] = strval(time() + 86400);
        }

        return response()->json($response);
    }
}