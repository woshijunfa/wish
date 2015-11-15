<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\User;
use Request;
use View;
use Log;
use Agent;
use Input;
use Auth;

class HomeController extends Controller
{
	//返回错误页面
	public function index(Request $request)
	{
		if (Auth::check()) 
		{
			var_dump("已经登录，用户信息");
			var_dump(Auth::user()->toArray());
		}
		else 
		{
			var_dump('尚未登录');
		}
	}
}

