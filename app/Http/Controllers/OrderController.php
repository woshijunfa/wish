<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\Order;
use App\Models\User;
use App\Models\Trade;
use App\Models\GlobalDef;
use App\Services\OrderService;
use App\Services\PayService;
use App\Services\CalendarService;
use Request;
use Redirect;
use View;
use Input;
use Log;
use Auth;

require_once(base_path().'/app/Libs/pingpp/init.php');

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

	public function ipayOrderGet(Request $request)
	{
		//获取订单信息
		$orderId = Input::get('order');
		if (empty($orderId)) return $this->errorPage('出错啦！');

		//获取订单信息
		$orderInfo = Order::getOrderInfoById($orderId);
		if (empty($orderInfo) || $orderInfo['user_id'] != Auth::id()) return $this->errorPage('出错啦！');

		//不需要支付的订单转到订单详情
		if($orderInfo['order_status'] != GlobalDef::ORDER_STATUS_INIT) return Redirect::to('/i/order/detial?order_id=' . $orderId);


		//获取对应的用户信息
		$partnerInfo = User::getUser($orderInfo['partner_id']);
		if (empty($partnerInfo))  return $this->errorPage('出错啦！');

		//补充信息
		$orderInfo['nickname'] = $partnerInfo['nickname'];
		$orderInfo['mobile'] = $partnerInfo['mobile'];
		$orderInfo['email'] = $partnerInfo['email'];
		$orderInfo['head_image'] = $partnerInfo['head_image'];

		return View::make('h5.payOrder',$orderInfo);
	}


	//获取支付对象页面
	public function getPayChangeObject(Request $request)
	{
		//获取订单信息
		$orderId = Input::get('order_id');
		$channel = Input::get('channel');

		if (empty($orderId) || empty($channel)) return $this->returnJsonResult(1,'出错了，请刷新重试');

		//获取订单信息
		$orderInfo = Order::getOrderInfoById($orderId);
		if (empty($orderInfo) || $orderInfo['user_id'] != Auth::id()) return $this->returnJsonResult(1,'出错了，请刷新重试');

		//不需要支付的订单转到订单详情
		if($orderInfo['order_status'] != GlobalDef::ORDER_STATUS_INIT) return $this->returnJsonResult(2,'订单状态有误',['url'=>'/order/detial?order_id=' . $orderId]);;

		$dates = explode(',', $orderInfo['order_dates']);
		//检查合作者的时间是否OK
		$isok = CalendarService::checkPartnerCalendar($orderInfo['partner_id'],$dates);
		if (!$isok) return $this->returnJsonResult(3,'付款晚了，导游那天没空了，亲看下其他时间试试吧');

		//检查用户的安排
		$isok = CalendarService::checkUserCalendar($orderInfo['user_id'],$dates);
		if (!$isok) return $this->returnJsonResult(4,'您预约的日期已经有自己的安排了');

		//生成对象
		$charge = PayService::getPingppObject($channel,$orderInfo);

		if (empty($charge)) return $this->returnJsonResult(5,'不支持该支付方式');

		return $this->returnJsonResult(0,'',['charge'=>$charge]);
	}



	//支付宝PC端返回支付结果
	public function onPayReturn(Request $request)
	{
		//获取参数
		$params = Input::get();
		Log::info('OrderController::onPayReturn params:'.json_encode($params));

		//获取order_no也就是trade_no
		$tradeNo = Input::get('out_trade_no'); 					//支付宝
		if (empty($tradeNo)) $tradeNo = Input::get('orderId');	//银联
		if (empty($tradeNo)) return $this->errorPage('出错啦');

		//获取trade_no对应额trade信息
		$tradeInfo = Trade::getTradeByTradeNo($tradeNo);
		if (empty($tradeInfo)) return $this->errorPage('出错啦');

		//验证是否支付成功
		$isok = PayService::checkPayByPingId($tradeInfo['ch_id']);
		if ($isok !== true) return $this->errorPage('出错啦');

		//修改订单支付状态
		$isSuccess = OrderService::succesPayOrder($tradeInfo);

		//为支付成功，重新转到订单支付页面
		if (!$isSuccess) return $this->errorPage('订单支付失败，重新支付？',['url'=>'/order/pay?order='.$tradeInfo['order_id'],'msg'=>'重新支付']);

		//跳转到订单详情页面
		return Redirect::to('order/detial?order_id=' . $tradeInfo['order_id']);
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


