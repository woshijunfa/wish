<?php

namespace App\Services;

use DB;
use App\Models\Calendar;
use App\Models\Order;
use App\Models\Utility;
use App\Models\GlobalDef;
use Config;

require_once(base_path().'/app/Libs/alipay/lib/alipay_submit.class.php');
require_once(base_path().'/app/Libs/alipay/lib/alipay_notify.class.php');
require_once(base_path().'/app/Libs/pingpp/init.php');

class PayService
{

	//【支付宝api】对订单信息进行签名，并生成跳转到支付宝的url
	//$cusId 			下单人的ID
	//$busId 			预约对象的id
	//$dates 			预约的日期
	public static function alipaySign($orderInfo)
	{
		//需要签名的参数列表

		/**************************请求参数**************************/

		$partner = Config::get('pay.alipay.partner');
		$seller_email = Config::get('pay.alipay.seller_email');
		$charset = Config::get('pay.alipay.input_charset');

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url =  Config::get('app.url')."/pay/alipay_notify";
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = Config::get('app.url')."/pay/alipay_return";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no = $orderInfo['order_id'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $orderInfo['subject'];
        //必填

        //付款金额
        $total_fee = $orderInfo['total_fee'];
        //必填

        //订单描述

        $body = $orderInfo['subject'];
        //商品展示地址
        $show_url = '';
        //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1


		/************************************************************/

		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "create_direct_pay_by_user",
				"partner" => $partner,
				"seller_email" => $seller_email,
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"		=> $body,
				"it_b_pay"	=> GlobalDef::ORDER_EXPIRE_MINUTE . 'm',
				"show_url"	=> $show_url,
				"anti_phishing_key"	=> $anti_phishing_key,
				"exter_invoke_ip"	=> $exter_invoke_ip,
				"_input_charset"	=> $charset
		);

		//支付宝配置
		$alipay_config = Config::get('pay.alipay');

		//建立请求
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

		//反对页面
		return $html_text;
	}

	//【支付宝api】 对订单返回的信息进行验证，验证通过，返回
	public static function alipayCheckReturnParams($params)
	{
		//支付宝配置
		$alipay_config = Config::get('pay.alipay');

		//建立请求
		$alipayNotify = new \AlipayNotify($alipay_config);

		//校验是否OK
		$result = $alipayNotify->verify($params);
		return $result;
	}


	public static function getPingppObject($channel,$info)
	{
		//获取pingxx配置
		$pingConfig = Config::get('pay.pingxx');

		\Pingpp\Pingpp::setApiKey($pingConfig['api_key']);

		$payInfo = [
			        'order_no'  => $info['order_no'],
			        'app'       => array('id' => $pingConfig['app_id']),
			        'channel'   => $channel,
			        'amount'    => (int)($info['total_fee']*100),
			        'client_ip' => Utility::getClientIp(),
			        'currency'  => 'cny',
			        'subject'   => $info['subject'],
			        'body'      => $info['subject'],
			        'extra'     => []
			    ];

		switch ($channel) {
			//pc-支付宝direct
			case GlobalDef::PAY_CHANNEL_ALIPAY_PC_DIRECT:
			case GlobalDef::PAY_CHANNEL_ALIPAY_WAP:
			$payInfo['extra'] = ['success_url'=> Config::get('app.url').'/pay/return'];
			break;
			case GlobalDef::PAY_CHANNEL_UPACP_WAP:
			case GlobalDef::PAY_CHANNEL_UPACP_PC:
			$payInfo['extra'] = ['result_url'=> Config::get('app.url').'/pay/return'];
			break;
			case GlobalDef::PAY_CHANNEL_WX:
			break;
			default:
				return false;
				break;
		}

		$ch = \Pingpp\Charge::create($payInfo);

		return $ch;
	}
}

