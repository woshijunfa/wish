<?php

namespace App\Http\Controllers;

use Request;
use View;
use Log;
use Agent;
use Input;

class UserController extends Controller
{
	//返回错误页面
	public static function regist(Request $request)
	{
		return View::make('pc.regist');
	}
}

