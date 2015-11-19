<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\User;
use App\Services\CalendarService;
use Request;
use View;
use Log;
use Agent;
use Input;
use Auth;



class CalendarController extends Controller
{
	public function getUserCalendar(Request $request)
	{
		//获取参数
		$userId = Input::get('user_id');
		$month = Input::get('month');

		//获取用户
		if (empty($userId)) return $this->returnJsonResult(1,'');
		if (empty($month)) $month = date('Y-m',time());
		if (!Utility::validMonth($month)) return $this->returnJsonResult(1,'');

		//调用
		$dates = CalendarService::getUserCalendarMonth($userId,$month,false);
		if (empty($dates)) return $this->returnJsonResult(2,'');

		return $this->returnJsonResult(0,'',['month'=>$month,'data'=>$dates]);	
	}


}

