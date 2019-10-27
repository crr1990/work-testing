<?php

namespace App\Services;
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/12
 * Time: 12:06 AM
 */
use Illuminate\Support\Facades\Mail;

class NoticeService
{

    /**
     * 发送邮件
     *
     * @param $password
     * @param $user
     */
    public function sendMail($password, $user)
    {
        return;
        Mail::raw("你的登录密码是{$password}", function ($message) use ($user) {
            $message->from("814258346@qq.com", "艾为图像设备（上海）有限公司");
            $message->to($user['email'], $user['name']);
            $message->subject("智能印前自动化系统平台密码重置");
        });
    }
}