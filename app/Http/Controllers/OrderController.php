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

class OrderController extends Controller
{
	//创建订单页面
	public function createOrder(Request $request)
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

	//订单支付页面
	public function payOrderGet(Request $request)
	{
		//获取订单信息
		$orderId = Input::get('order');
		if (empty($orderId)) return $this->errorPage('出错啦！');

		//获取订单信息
		$orderInfo = Order::getOrderInfoById($orderId);
		if (empty($orderInfo) || $orderInfo['user_id'] != Auth::id()) return $this->errorPage('出错啦！');

		//不需要支付的订单转到订单详情
		if($orderInfo['order_status'] != GlobalDef::ORDER_STATUS_INIT) return Redirect::to('/order/detial?order_id=' . $orderId);

		//获取对应的用户信息
		$partnerInfo = User::getUser($orderInfo['partner_id']);
		if (empty($partnerInfo))  return $this->errorPage('出错啦！');

		//补充信息
		$orderInfo['nickname'] = $partnerInfo['nickname'];
		$orderInfo['mobile'] = $partnerInfo['mobile'];
		$orderInfo['email'] = $partnerInfo['email'];
		$orderInfo['head_image'] = $partnerInfo['head_image'];

		return View::make('pc.payOrder',$orderInfo);
	}

	//alipay页面跳转返回
	public function onAlipayReturn(Request $request)
	{
		//获取参数
		$params = Input::get();
		Log::info('OrderController::onAlipayReturn params:'.json_encode($params));

		$isSuccess = self::alipayProcess($params);


	}

	//alipay后台通知支付结果
	public function onAlipayNotify(Request $request)
	{
		//获取参数
		$params = Input::get();
		Log::info('OrderController::onAlipayNotify params:'.json_encode($params));

	}

	//订单成功支付处理
	private function alipayProcess($params)
	{
		//校验是否是支付宝返回的接口
		$isok = PayService::alipayCheckReturnParams($params);
		if (!$isok) return ['code'=>0,'desc'=>'支付宝校验失败'];

		//没有支付成功
		if ($params['trade_status'] != 'TRADE_FINISHED' && 
			$params['trade_status'] != 'TRADE_SUCCESS') 
		{
			return ['code'=>0,'desc'=>'没有支付成功'];
		}

		//进行支付逻辑处理
		$orderId = $params['out_trade_no'];

		//进行状态更改
		$result = OrderService::payOrder($orderId);
		if ($result !== true) 
		{
			
		}


	}
}


