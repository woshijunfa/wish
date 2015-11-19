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

Route::get('/', "HomeController@index");

Route::get('/regist',"UserController@regist");
Route::post('/user/regist',"UserController@doRegist");

Route::get('/login',"UserController@login");
Route::post('/user/login',"UserController@doLogin");

Route::get('/user/resetLoginPass',"UserController@resetLoginPass");
Route::post('/user/doResetLoginPass',"UserController@doResetLoginPass");
Route::post('/user/getUserCalendar','CalendarController@getUserCalendar');



//验证码相关
Route::get('/verify/getimg','VerifyController@getimg');
Route::post('/verify/checkimg', 'VerifyController@checkimg');
Route::post('/verify/getphonecode', 'VerifyController@sendPhoneCode');


//需要登录才有的功能
Route::group(['middleware' => 'auth'], function ()
{
	//订单相关
	Route::any('/order/createOrder','OrderController@createOrder');
});


Route::get('/test', "TestController@test");

