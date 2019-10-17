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

$app->post('test', 'ExampleController@test');
$app->post('login', 'UserController@login');
$app->post('upload', 'UploadController@upload');
$app->group(['prefix' => 'user', 'middleware' => 'auth:api'], function () use ($app) {
//$app->group(['prefix'=>'user'],function () use ($app){
    $app->post('logout', 'AuthController@logout');
    $app->post('refresh', 'AuthController@refreshToken');
    $app->post('register', 'UserController@addUser');
    $app->post('list', 'UserController@userList');
    $app->post('info', 'UserController@userInfo');
});


$app->post('orderTemplate/create', 'OrderTemplateController@createTemp');
$app->post('orderTemplate/edit', 'OrderTemplateController@editTemp');
$app->post('orderTemplate/delete', 'OrderTemplateController@deleteTemp');
$app->post('orderTemplate/editParam', 'OrderTemplateController@editTempParam');
$app->post('orderTemplate/appendParam', 'OrderTemplateController@appendParam');
$app->post('orderTemplate/deleteParam', 'OrderTemplateController@deleteParam');
$app->post('orderTemplate/setIcon', 'OrderTemplateController@setIcon');
$app->get('orderTemplate/list', 'OrderTemplateController@lists');

$app->post('job/jobList', 'OrderController@getJobList');
$app->post('job/deleteJob', 'OrderController@deleteJob');
$app->post('job/createJob', 'OrderController@createJob');
$app->post('job/editJob', 'OrderController@editJob');




