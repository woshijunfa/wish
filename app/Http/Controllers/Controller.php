<?php

namespace App\Http\Controllers;

use Request;
use View;
use Log;
use Agent;
use Input;

class Controller extends BaseController
{
	//返回错误页面
	//$msg 错误提醒信息
	//$extInfo = ['url'=>'跳转的url','msg'=>'']
	// url 要跳转的url
	// msg 跳转提示信息
	protected function errorPage($msg,$extInfo=array())
	{
		$desc = empty($msg) ? '发生未知错误' : $msg;
		$pageInfo = array();
		$pageInfo['info'] = $desc;
		$pageInfo['url'] = '/';

		return View::make('pc.error',$pageInfo);
	}

	public function error(Request $request)
	{
		return $this->errorPage(Input::get('desc'));
	}
}

