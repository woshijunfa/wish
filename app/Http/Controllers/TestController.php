<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\User;
use App\Services\CalendarService;
use Request;
use View;

class TestController extends Controller
{
	//返回错误页面
	public function test(Request $request)
	{
		$cals = CalendarService::getUserCalendarMonth(1,'2015-11');
		return View::make('pc.test',['cals'=>$cals,'month'=>'2015-11']);
	}

}

