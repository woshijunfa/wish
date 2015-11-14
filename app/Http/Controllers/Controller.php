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
	protected function errorPage($msg,$extInfo=array())
	{
		$desc = empty($msg) ? '发生未知错误' : $msg;

//		Log::info('GATE_SERVICE_FAILED ' . $msg . ' Request Info:' . var_export(Input::get(),true) . ' ADD_INFO:' . var_export($info,true));

		$isMobile = Agent::isMobile();

		$tpl = $isMobile ? 'h5.error' : 'pc.error';

		$pageInfo = array();
		$pageInfo['info'] = $desc;
		if (is_array($extInfo) && array_key_exists('url',$extInfo) && !empty($extInfo['url']))  $pageInfo['url'] = $extInfo['url'];
		else $pageInfo['url'] = 'http://www.qufenqi.com';

		return View::make($tpl,$pageInfo);
	}

	public function error(Request $request)
	{
		return $this->errorPage(Input::get('desc'));
	}
}

