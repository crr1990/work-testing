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

class UserController extends Controller
{
    public function addUser(Request $request)
    {
        $name = $request->input("name");
        $email = $request->input("email");
        $allowCapacity = $request->input("allowCapacity");
        (new UserInfoService())->register($name, $email,$allowCapacity);
        return response()->json([
            "code" => 1,
            "message" => "success"
        ]);
    }
}