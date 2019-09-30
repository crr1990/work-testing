<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/30
 * Time: 10:42 PM
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller;

/**
 * @author AdamTyn
 * @description <用户客户端相关>控制器
 */
class ClientController extends Controller
{
    /**
     * 用户客户端仓库的单例
     * @var \Laravel\Passport\ClientRepository
     */
    private static $clientRepository = null;

    /**
     * 用户模型
     * @var \App\Models\UserModel
     */
    private static $userModel = null;

    /**
     * 当前时间戳
     * @var int
     */
    protected $currentDateTime;

    /**
     * 授权方式：password=密码授权的令牌，personal=私人授权的令牌
     * @var string
     */
    protected $grantType = 'password';

    /**
     * @author AdamTyn
     *
     * AuthController constructor.
     */
    public function __construct()
    {
        empty(self::$userModel) ? (self::$userModel = (new \App\Models\UserModel)) : true;
        $this->currentDateTime = time();
    }

    /**
     * @author AdamTyn
     * @description 用户注册（默认密码123456）
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $field = [
            'user_name' => $request->get('user_name') ?? time(),
            'password' => Hash::make($request->get('password') ?? '123456')
        ];

        $user = (self::$userModel)->create($field);
        $response['data'] = $user;

        return response()->json($response);
    }

    /**
     * @author AdamTyn
     * @description 添加使用密码认证的客户端
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function registerPasswordClient(Request $request)
    {
        $response = array('status_code' => '2000');

        try {
            $user = (self::$userModel)->whereUserName($request->input('user_name'))->firstOrFail();
            $this->createClient($response, $request, $user);
        } catch (\Exception $exception) {
            $response = [
                'status_code' => '5002',
                'msg' => '无法响应请求，服务端异常',
            ];
            Log::error($exception->getMessage() . ' at' . $this->currentDateTime);
        }

        return response()->json($response);
    }

    /**
     * @author AdamTyn
     * @description 登录私人访问的客户端
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function loginPersonalClient(Request $request)
    {
        $response = array('status_code' => '2000');
        $this->changeGrantType();

        try {
            $user = (self::$userModel)->whereUserName($request->input('user_name'))->firstOrFail();
            if (Hash::check($request->input('password'), $user->getAuthPassword())) {
                $response['data'] = $user->createToken(data_get($request, 'token_name', $user->user_name . '`s token_name'));
            } else {
                $response = [
                    'status_code' => '5000',
                    'msg' => '系统错误',
                ];
            }
        } catch (\Exception $exception) {
            $response = [
                'status_code' => '5002',
                'msg' => '无法响应请求，服务端异常',
            ];
            Log::error($exception->getMessage() . ' at' . $this->currentDateTime);
        }

        return response()->json($response);
    }

    /**
     * @author AdamTyn
     * @description 添加私人访问的客户端
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function registerPersonalClient(Request $request)
    {
        $response = array('status_code' => '2000');
        $this->changeGrantType();

        try {
            $user = (self::$userModel)->whereUserName($request->input('user_name'))->firstOrFail();
            $this->createClient($response, $request, $user);
        } catch (\Exception $exception) {
            $response = [
                'status_code' => '5002',
                'msg' => '无法响应请求，服务端异常',
            ];
            Log::error($exception->getMessage() . ' at' . $this->currentDateTime);
        }

        return response()->json($response);
    }

    /**
     * @author AdamTyn
     * @description 初始化用户客户端仓库的单例
     */
    private function initialClientRepository()
    {
        empty(self::$clientRepository) ? (self::$clientRepository = (new ClientRepository)) : true;
    }

    /**
     * @author AdamTyn
     * @description 改变授权类型为私人访问
     */
    private function changeGrantType()
    {
        $this->grantType = 'personal';
    }

    /**
     * @author AdamTyn
     * @description 新增用户客户端记录
     *
     * @param array $response
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\UserModel $user
     */
    private function createClient(&$response, $request, $user)
    {
        $this->initialClientRepository();

        if (Hash::check($request->input('password'), $user->getAuthPassword())) {
            # createPasswordGrantClient: 创建密码获取token访问的客户端记录
            # createPersonalAccessClient: 创建私人访问的客户端记录
            $func = ($this->grantType === 'password') ? 'createPasswordGrantClient' : 'createPersonalAccessClient';
            $client = self::$clientRepository->$func(
                $user->id,
                $user->user_name . '`s new ' . $this->grantType . ' client',
                data_get($request, 'redirect', 'http://localhost:8000')
            );

            $response['data'] = [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_grant_type' => $this->grantType,
                'client_secret' => $this->grantType ? $client->secret : null,
                'client_redirect' => $client->redirect,
            ];
        } else {
            $response = [
                'status_code' => '5000',
                'msg' => '系统错误',
            ];
        }
    }
}