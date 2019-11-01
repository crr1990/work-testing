<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$app->get('send', 'ExampleController@sendMail');
$app->get('/', function () {

    return 'Hello World';
});

$app->get('test', 'ExampleController@test');
$app->post('login', 'UserController@login');
$app->post('upload', 'UploadController@upload');
$app->post('captchaInfo', 'UserController@captchaInfo');
$app->post('checkCaptcha', 'UserController@check');
$app->post('user/resetPassword', 'UserController@resetPassword');

$app->group(['prefix' => 'user', 'middleware' => 'auth:api'], function () use ($app) {
    $app->post('logout', 'AuthController@logout');
    $app->post('refresh', 'AuthController@refreshToken');
    $app->post('register', 'UserController@addUser');
    $app->post('editUserInfo', 'UserController@editUser');
    $app->post('list', 'UserController@userList');
    $app->post('info', 'UserController@userInfo');
    $app->post('delete', 'UserController@delete');

});

$app->group(['prefix' => 'orderTemplate', 'middleware' => 'auth:api'], function () use ($app) {
    $app->post('create', 'OrderTemplateController@createTemp');
    $app->post('edit', 'OrderTemplateController@editTemp');
    $app->post('delete', 'OrderTemplateController@deleteTemp');
    $app->post('editParam', 'OrderTemplateController@editTempParam');
    $app->post('appendParam', 'OrderTemplateController@appendParam');
    $app->post('deleteParam', 'OrderTemplateController@deleteParam');
    $app->post('setIcon', 'OrderTemplateController@setIcon');
    $app->post('list', 'OrderTemplateController@lists');
});

$app->group(['prefix' => 'job', 'middleware' => 'auth:api'], function () use ($app) {
    $app->post('jobList', 'OrderController@getJobList');
    $app->post('deleteJob', 'OrderController@deleteJob');
    $app->post('createJob', 'OrderController@createJob');
    $app->post('editJob', 'OrderController@editJob');
    $app->post('copyJob', 'OrderController@copy');
});







