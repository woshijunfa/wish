<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\User;
use App\Models\Order;
use App\Services\CalendarService;
use App\Services\OrderService;
use App\Services\PayService;
use Request;
use View;

require_once(base_path().'/app/Libs/pingpp/init.php');



class TestController extends Controller
{
	public function test(Request $request)
	{
		// $orderInfo =  Order::getOrderInfoById(1);
		// $rtnstr = PayService::alipaySign($orderInfo);

		// echo $rtnstr;
		// die;

		$cals = CalendarService::getUserCalendarMonth(1,'2015-11');
		return View::make('pc.test',['cals'=>$cals,'month'=>'2015-11']);
	}

	//返回错误页面
	public function test2(Request $request)
	{
		\Pingpp\Pingpp::setApiKey('sk_test_yHKS84aDabzLj5SiDCrLif58');

		//pc-支付宝direct
		$ch = \Pingpp\Charge::create(
									    array(
									        'order_no'  => '1'.time(),
									        'app'       => array('id' => 'app_vr1mjDqP8un1LWP0'),
									        'channel'   => 'alipay_pc_direct',
									        'amount'    => 100,
									        'client_ip' => '127.0.0.1',
									        'currency'  => 'cny',
									        'subject'   => '铅笔名',
									        'body'      => '2b铅笔',
									        'extra'     => [
									        	'success_url'=>'http://www.wish.com/pay/alipay_return'
									        ]
									    )
									);

		// //银联支付
		// $ch = \Pingpp\Charge::create(
		// 							    array(
		// 							        'order_no'  => '1'.time(),
		// 							        'app'       => array('id' => 'app_vr1mjDqP8un1LWP0'),
		// 							        'channel'   => 'upacp_pc',
		// 							        'amount'    => 100,
		// 							        'client_ip' => '127.0.0.1',
		// 							        'currency'  => 'cny',
		// 							        'subject'   => '铅笔名',
		// 							        'body'      => '2b铅笔',
		// 							        'extra'     => []
		// 							    )
		// 							);



		return View::make('pc.paytest',['charge'=>$ch]);
//		echo($ch);

		die;


		$orderInfo =  Order::getOrderInfoById(1);
		$rtnstr = PayService::alipaySign($orderInfo);

		echo $rtnstr;
		die;

		$cals = CalendarService::getUserCalendarMonth(1,'2015-11');
		return View::make('pc.test',['cals'=>$cals,'month'=>'2015-11']);
	}

}

