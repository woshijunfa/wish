<?php

namespace App\Services;

use DB;
use App\Models\Calendar;
use App\Models\Trade;
use App\Models\Utility;
use App\Models\GlobalDef;

class TradeService
{

	//根据pingxx生成的交易表
	//$pingCharge 			pingCharge对象
	//$orderInfo 			order信息
	//$tradeNo 				生成的交易号 				

	public static function createByPingCharge($pingCharge,$orderInfo,$tradeNo)
	{
		if (empty($pingCharge) || empty($orderInfo) || empty($tradeNo)) return false;

		$chargeInfo = [];
		$chargeInfo['trade_no'] = $tradeNo;
		$chargeInfo['ch_id'] 	= $pingCharge['id'];
		$chargeInfo['order_id'] 	= $orderInfo['order_id'];
		$chargeInfo['channel'] 	= $pingCharge['channel'];
		$chargeInfo['amount'] =	$orderInfo['total_fee'];

		return Trade::createCharge($chargeInfo);
	}

}


