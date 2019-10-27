<?php

namespace App\Services;

use App\Models\OrderTemplateUser;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/8/19
 * Time: 2:48 PM
 */
class UserInfoService
{

    public function userList($filter, $limit, $offset, $page)
    {
        $user = User::where("is_enabled", 1);
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $user->where("name", "like", $filter["name"]);
        }

        if (isset($filter["userId"]) && !empty($filter["userId"])) {
            $user->where("id", $filter["userId"]);
        }

        if (isset($filter["email"]) && !empty($filter["email"])) {
            $user->where("email", $filter["email"]);
        }

        $total = $user->count();
        $totalPage = ceil($total / $limit);

        $list = $user->limit($limit)->offset($offset)->get()->toArray();
        $orderTemp = new OrderTemplateService();
        foreach ($list as $k=>$v) {
            $list[$k]['template'] = $orderTemp->tempIdByUser($v['id']);
        }

        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                "list" => $list,
                "currentPage" => $page,
                "totalPage" => $totalPage,
                "pageSize" => $limit,
                "total" => $total
            ]
        ];
    }

    public function register($name, $email, $allowCapacity, $desc, $password,$type,$template,$isEnabled)
    {
//        if ($this->checkIsExistByEmail($email)) {
//            return [
//                'code' => 2001,
//                'msg' => '邮箱已注册'
//            ];
//        }
//
//        if ($this->checkIsExistByName($name)) {
//            return [
//                'code' => 2002,
//                'msg' => '用户名已注册'
//            ];
//        }

        $password = empty($password) ? rand(100000, 999999) : $password;

        $user = [
            "name" => $name,
            "allow_capacity" => $allowCapacity * 1024 * 1024,
            "email" => $email,
            "password" => md5($password),
            "type" => $type,
            "desc" => $desc,
            "is_enabled" => $isEnabled
        ];

        $result = User::create($user);

        if ($result->id > 0) {

            try {
                (new NoticeService())->sendMail($password, $user);
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }

            $templateUser = [];
            // 分配模板
            foreach ($template as $v) {
                $templateUser[] = [
                    "temp_id"=> $v,
                    "user_id" => $result->id
                ];
            }

            if($templateUser) {
                \DB::table('order_template_user')->insert($templateUser);
            }
        }

        return [
            'code' => 0,
            'msg' => 'success'
        ];

    }

    public function login($name, $password)
    {
        $response = array('code' => 0);

        try {
            $user = $this->checkUser($name, $password);
            if (!$user) {
                $response['code'] = 1001;
                $response['message'] = '账户名或者密码错误';
            } else if (!$token = Auth::login($user)) {
                $response['code'] = 1002;
                $response['message'] = '系统错误，无法生成令牌';
            } else {
                $response['data']['user_id'] = strval($user->id);
                $response['data']['user_type'] = $user->type;
                $response['data']['access_token'] = $token;
                $response['data']['expires_in'] = strval(time() + 86400);
            }
        } catch (QueryException $queryException) {
            $response['code'] = 1003;
            $response['message'] = '无法响应请求，服务端异常';
        }

        return $response;
    }


    public function checkUser($name, $password)
    {
        return User::where("name", $name)->where("password", md5($password))->first();
    }

    public function checkIsExistByName($name)
    {
        return User::where("name", $name)->first() ? true : false;
    }

    public function checkIsExistByEmail($email)
    {
        return User::where("email", $email)->first() ? true : false;
    }

    public function editUserInfo($name, $email, $allowCapacity, $password, $isEnabled, $id, $template)
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        $user->name = empty($name) ? $user->name : $name;
        $user->is_enabled = $isEnabled != 1 ? $user->is_enabled : $isEnabled;
        $user->email = empty($email) ? $user->email : $email;
        $user->allow_capacity = empty($allowCapacity) ? $user->allow_capacity : $allowCapacity;
        $user->password = empty($password) ? $user->password : $password;
        $user->save();

        if(md5($password) != $user->password) {
            (new NoticeService())->sendMail($password, $user);
        }

        if(!$template){
            \DB::delete('delete from order_template_user where user_id = ?',[$id]);
            $templateUser = [];

            // 分配模板
            foreach ($template as $v) {
                $templateUser[] = [
                    "temp_id"=> $v,
                    "user_id" => $id
                ];
            }

            if($templateUser) {
                \DB::table('order_template_user')->insert($templateUser);
            }
        }

        return true;
    }

    /**
     * 密码重置
     *
     * @param $name
     * @param $email
     * @return bool
     */
    public function resetPassword($name, $email)
    {
        $user = User::where("email", $email)->first();
        if (!$user || $user->name != $name) {
            return false;
        }

        $user->password = md5(round(100000, 999999));
        $user->save();

        (new NoticeService())->sendMail($user->password, $user);
        return true;
    }
}