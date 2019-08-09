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
$app->get('send','ExampleController@sendMail');

$app->post('login','AuthController@login');
$app->group(['prefix'=>'/','middleware'=>'auth:api'],function () use ($app){
    $app->post('logout','AuthController@logout');
    $app->post('refresh','AuthController@refreshToken');
});




