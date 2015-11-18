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



class CalendarController extends Controller
{
	public function getUserCalendar(Request $request)
	{
		return $this->returnJsonResult(0,'',['month'=>'015-10-21','data'=>[]])		
	}


	//返回错误页面
	public function regist(Request $request)
	{
		$url = '/';
		if (!empty(Input::get('callback'))) {
			$url = Input::get('callback');
		}

		return View::make('pc.regist',['url'=>$url]);
	}

	public function doRegist(Request $request)
	{
		$mobile = Input::get('mobile');
		$mobileCode = Input::get('mobile_code');
		$password = Input::get('password');

		if (empty($mobile) || empty($mobileCode) || empty($password)) 
		{
			return $this->returnJsonResult(1,'参数错误');
		}

		$isok = Utility::checkFormatMobileNum($mobile);
		if (!$isok) 
		{
			return $this->returnJsonResult(2,'手机号码错误');
		}

		$isok = Utility::checkPhoneCode($mobile,$mobileCode);
		if (!$isok) 
		{
			return $this->returnJsonResult(3,'手机验证码错误');
		}

		$array = [
			'mobile' 	=> $mobile,
			'password'  => $password,
			'nickname'  => $mobile
		];
		
		$user_id = User::insertUser($array);
		if (empty($user_id)) 
		{
			return $this->returnJsonResult(4,'注册失败，请稍后再试');
		}
		
		Auth::loginUsingId($user_id);

		return $this->returnJsonResult(0,'');
	}

	public function login(Request $request)
	{
		$url = '/';
		if (!empty(Input::get('callback'))) {
			$url = Input::get('callback');
		}

		return View::make('pc.login',['url'=>$url]);
	}

	public function doLogin(Request $request)
	{
		$mobile = Input::get('mobile');
		$password = Input::get('password');
		$rememberMe = Input::get('remember_me') == "true";

		if (empty($mobile) || empty($password)) 
		{
			return $this->returnJsonResult(1,'参数错误');
		}

		$isok = Utility::checkFormatMobileNum($mobile);
		if (!$isok) 
		{
			return $this->returnJsonResult(2,'手机号码错误');
		}

		if (!Auth::attempt(['mobile'=>$mobile,'password'=>$password],$rememberMe)) 
		{
			return $this->returnJsonResult(3,'手机号或者密码错误');
		}

 		return $this->returnJsonResult(0,'');
	}

	public function resetLoginPass(Request $request)
	{
		return View::make('pc.resetLoginPass');
	}

	public function doResetLoginPass(Request $request)
	{
		$mobile = Input::get('mobile');
		$mobileCode = Input::get('mobile_code');
		$password = Input::get('password');

		if (empty($mobile) || empty($mobileCode) || empty($password)) 
		{
			return $this->returnJsonResult(1,'参数错误');
		}

		$isok = Utility::checkFormatMobileNum($mobile);
		if (!$isok) 
		{
			return $this->returnJsonResult(2,'手机号码错误');
		}

		$isok = Utility::checkPhoneCode($mobile,$mobileCode);
		if (!$isok) 
		{
			return $this->returnJsonResult(3,'手机验证码错误');
		}

		$user_id = User::resetUserPasswordByAccount($mobile,$password);
		if (empty($user_id)) 
		{
			return $this->returnJsonResult(4,'修改失败，请稍后再试');
		}
		
		return $this->returnJsonResult(0,'');
	}
}

