<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\Order;
use App\Models\User;
use App\Models\GlobalDef;
use App\Services\OrderService;
use App\Services\PayService;
use Request;
use View;
use Input;
use Auth;

class PayController extends Controller
{
	//生成支付订单
	public function getPayChangeObject(Request $request)
	{
		$userId = Auth::id();
		$partnerId = Input::get('user_id');
		$dates = Input::get('dates');

		//参数校验
		if (empty($userId) || empty($partnerId) || empty($dates)) return $this->returnJsonResult(1,'参数错误');	

		//生成订单
		$result = OrderService::order($userId,$partnerId,$dates);
		if ($result['code'] != 0) return $this->returnJsonResult($result['code'],$result['desc']);

		//生成url，去付款
		return $this->returnJsonResult(0,'',['url'=>'/order/pay?order='.$result['data']]);
	}

}


