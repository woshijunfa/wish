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

//验证码相关
Route::get('/verify/getimg','VerifyController@getimg');
Route::get('/verify/checkimg', 'VerifyController@checkimg');
Route::any('/verify/getphonecode', 'VerifyController@sendPhoneCode');

