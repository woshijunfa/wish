<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/regist',"UserController@regist");
Route::post('/user/regist',"UserController@doRegist");

//验证码相关
Route::get('/verify/getimg','VerifyController@getimg');
Route::post('/verify/checkimg', 'VerifyController@checkimg');
Route::post('/verify/getphonecode', 'VerifyController@sendPhoneCode');

