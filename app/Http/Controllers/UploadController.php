<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/7
 * Time: 4:54 PM
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class UploadController
{
    function upload(Request $request) {
        //1.文件上传路径
        $dir = $request->input("dir_path","");
    }
}