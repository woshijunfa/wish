<?php

namespace App\Services;

use DB;
use App\Models\Calendar;
use App\Models\Order;
use App\Models\Utility;
use App\Models\GlobalDef;

class OrderService
{

	//用户下订单预约某人的日期
	//$cusId 			下单人的ID
	//$busId 			预约对象的id
	//$dates 			预约的日期
	public static function order($cusId,$busId,$dates)
	{
		//参数判断
		if (empty($cusId) || empty($busId) || empty($dates)) return ['code'=>1,'desc'=>'参数错误'];
		if ($cusId == $busId) return ['code'=>2,'desc'=>'不能预约自己的旅程'];

		if (!is_array($dates)) $dates = array($dates);

		//check并生成订单人的行程表
		$isok = CalendarService::checkUserCalendar($cusId,$dates);
		if ($isok != true) return ['code'=>3,'desc'=>'亲，这天你已经有安排了！'];

		//获取基本信息
		$busCals = Calendar::getCalByDates($busId,$dates);
		if (count($busCals) != count($dates)) return ['code'=>4,'desc'=>'选定日期有未开放日期'];

		$totalPrice = 0;
		foreach ($busCals as $value) 
		{
			//判断日期
			if ($value['date'] < date('Y-m',time())) return ['code'=>5,'desc'=>'只能预定今天或之后的时间'];
			if ($value['status'] != 'free') return ['code'=>6,'desc'=>'选定日期有已预约'];
			if ($value['price'] <= 1) return ['code'=>7,'desc'=>'用户未设定开放日期价格'];
			$totalPrice += $value['price'];
		}

        try{

        	//插入订单
        	$orderId = self::createOrder(['user_id'=>$cusId],
        								['user_id'=>$busId],
        								$dates,
        								$totalPrice);
        	
        	if (empty($orderId))
        	{
        		Log::info('生成订单失败 info:' . json_encode(['user_id'=>$cusId,'user_id'=>$busId,'dates'=>$dates,'totalprice'=>$totalPrice]));
	        	throw new Exception("生成订单失败", 1);
        	}


            //插入成功，返回tradeid
            return ['code'=>0,'data'=>$orderId];

        } catch(\Exception $e) {
            Utility::LogException($e);
            return ['code'=>8,'desc'=>'生成订单失败，请重新尝试'];
        }
	}


	//生成订单
	//$cusInfo 					下单人的信息
	//$busInfo 					导游的信息
	//$dates 					预定的日期
	//$totalFee 				总金额
	//成功返回订单id，否则返回空
	public static function createOrder($cusInfo,$busInfo,$dates,$totalFee)
	{
		$order['subject'] = count($dates) . '日游';
		$order['total_fee'] = $totalFee;
		$order['service_fee'] = $totalFee * GlobalDef::SERVICE_FEE_PERCENT / 100;
		$order['partner_fee'] = $totalFee - $order['service_fee'];
		$order['user_id'] = $cusInfo['user_id'];
		$order['partner_id'] = $busInfo['user_id'];
		$order['status'] = GlobalDef::ORDER_STATUS_INIT;
		$order['order_dates'] = $dates;

		return Order::createOrder($order);
	}

	public static function payOrder($orderId)
	{
    	//更新行程单
    	$isok = Calendar::orderCalendar($cusId,$busId,$dates,$orderId);
    	if (true !== $isok) 
    	{
    		Log::info('订单行程单失败 info:' . json_encode(['user_id'=>$cusId,'user_id'=>$busId,'dates'=>$dates,'totalprice'=>$totalPrice]));
        	throw new Exception("订单行程单失败", 1);
    	}
	}

}


