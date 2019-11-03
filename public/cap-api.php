<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/11/2
 * Time: 11:15 PM
 */

session_start();

$dataObj = json_decode($HTTP_RAW_POST_DATA);
if(strtolower($_SESSION["code"]) != strtolower($dataObj->captcha)){
    $msg = '不正确';
}else{
    //正确
}