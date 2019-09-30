<?php

namespace App\Services;

use App\Models\User;

/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/7
 * Time: 4:47 PM
 */
class FileService
{
    public function afterUpload($fileSize, $userId)
    {
        return User::find($userId)->increment("allow_capacity", $fileSize / 1024);
    }
}