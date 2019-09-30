<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/8/19
 * Time: 2:53 PM
 */

namespace App\Http\Controllers;

use App\Services\UserInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function list(Request $request) {
        var_dump($request->all());
    }

    /**
     * 增加用户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUser(Request $request)
    {
        $name = $request->input("name");
        $email = $request->input("email");
        $allowCapacity = $request->input("allowCapacity", 1);
        $desc = $request->input("desc", "");
        $password = $request->input("password", "");
        $res = (new UserInfoService())->register($name, $email, $allowCapacity, $desc, $password);

        return response()->json([
            "code" => $res['code'],
            "message" => $res['msg']
        ]);
    }

    /**
     * 用户登录
     *
     * @param Request $request
     * @param UserInfoService $userInfoService
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, UserInfoService $userInfoService)
    {
        $name = $request->input("name");
        $password = $request->input("password");
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "password" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 1004,
                "message" => $validator->errors()->first()
            ]);
        }

        $response = $userInfoService->login($name, $password);
        return response()->json($response);
    }


    /**
     * 用户编辑
     *
     * @param Request $request
     * @param UserInfoService $userInfoService
     * @return \Illuminate\Http\JsonResponse
     */
    public function editUser(Request $request, UserInfoService $userInfoService)
    {
        $id = $request->input("userId", "");
        $name = $request->input("name", "");
        $email = $request->input("email", "");
        $allowCapacity = $request->input("allowCapacity", "");
        $password = $request->input("password", "");
        $isEnabled = $request->input("isEnabled", 1);
        $response = $userInfoService->editUserInfo($name, $email, $allowCapacity, $password, $isEnabled, $id);
        return response()->json($response);
    }

    public function resetPassword(Request $request, UserInfoService $userInfoService)
    {
        $email = $request->input("email", "");
        $name = $request->input("name", "");
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code" => 1010,
                "message" => $validator->errors()->first()
            ]);
        }

        $res = $userInfoService->resetPassword($name, $email);
        if ($res) {
            return response()->json([
                "code" => 0,
                "message" => 'success'
            ]);
        } else {
            return response()->json([
                "code" => 1011,
                "message" => '用户名和邮箱匹配失败'
            ]);
        }

    }

}