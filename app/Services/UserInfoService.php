<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/8/19
 * Time: 2:48 PM
 */
class UserInfoService
{
    public function register($name, $email, $allowCapacity)
    {
        $password = rand(100000, 999999);
        $user = [
            "name" => $name,
            "allow_capacity" => $allowCapacity,
            "email" => $email,
            "password" => md5($password)
        ];

        Mail::raw("你的登录密码是{$password}",function ($message) use ($user) {
            $message->from("814258346@qq.com","HAHAHA");
            $message->to($user["email"],$user["name"]);
            $message->subject("xx公司欢迎您的加入");
        });


        User::create($user);
    }
}