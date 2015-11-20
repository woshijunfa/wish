<?php
/**
 * Created by PhpStorm.
 * User: wangjunfeng
 * Date: 2015/5/8 0008
 * Time: 17:38
 */
namespace Service\Pay;

use Log;
use Config;
use Cache;
use Auth;
use PayLog;
use PayApiLog;
use App;
use Input;
use Request;
use Utility;
use Service\Exception;

class AppAlipay
{

    //创建支付信息
    public function createPayMethod($pay)
    {
        //获取支付配置
        $configs = self::getAppAliaPayConfig($pay);
        // 记住支付方式
        Cache::forever('payway_'.Auth::id(), 'alipayapp');

        $money = round($pay->total_fee, 2);
        $subject = $pay->subject;
        $body = $pay->body;

        if($money<=0){
            return false;
        }

        $create_time = $pay->create_time;
        $create_time = date_create($create_time);
        $now = date_create();
        $minutes = date_diff($now, $create_time);
        $no = floor((($minutes->format("%i"))/30)+1);
        $out_trade_no = $pay->out_trade_no.'_al_'.$no;
        //记录支付支付日志
        \DBConnection\Router::forceOnMaster();
        $pay_log_item = PayLog::where('out_trade_no', $out_trade_no)->first();
        \DBConnection\Router::resetToDefault();
        if (empty($pay_log_item)) {
            $bill_log = array(
                'action_id'      =>$pay->action_id,
                'out_trade_no' =>$out_trade_no,
                'pay_way'      => 'appalipay',
                'ip'           => Utility::getClientIp(),
                'action'       =>$pay->action,
                'money'        => $pay->total_fee,
                'pay_method'  => array_get(PayLog::$dict_pay_method, 'appalipay', 'undefined'),
                'partner'      => $pay->partner_id,
            );

            $pay_log = new PayLog($bill_log);
            $pay_log->save();
        }

        if(empty($out_trade_no) || !$out_trade_no){
            return false;
        }
        //生成调起支付宝skd数据
        $params = array(
            'service'        => 'mobile.securitypay.pay',
            'partner'        => $configs['partner'],
            '_input_charset' => trim(strtolower($configs['input_charset'])),
            'notify_url'     => urlencode($configs['notify_url']),
            'out_trade_no'   => $out_trade_no,
            'subject'        => $subject,
            'payment_type'   => '1',
            'seller_id'      => $configs['seller_id'],
            'total_fee'      => $money,
            'body'           => $body,
            'it_b_pay'       => 30,
        );

        $string = self::_params2String($params);
        //添加签名
        $string = $this->_addSign($string, '"', $configs);
        $results['code']    = 0;
        $results['data']    = $string;
        $results['message'] = '';
        $results['url']     = "http://".$_SERVER['HTTP_HOST']."/i/pay/appreturnsuccess?out_trade_no=".$pay->out_trade_no."&go_root=1";
        echo json_encode($results);
        exit;
    }

    /**
     * 异步回调
     *
     * @abstract
     * @access public
     * @return string
     */
    public function notifyPayMethod ($pay_area) {
        //获取配置文件
        $config = Config::get('pay');
        $configs = $config[$pay_area]['alipayapp'];
        //获取回调请求数据
        $params = Input::all();
        //记录回调log
        App::make('Service\Pay')->writePayLog('PAY_RAW_LOG', array('url'=>Request::url(), 'params'=>$params));
        $pay_way = 'appalipay';

        try {
            $sign = $params['sign'];
            unset($params['sign']);
            unset($params['sign_type']);
            $string = self::_params2String($params, '');

            if (!$this->_verifySign($string, $sign, $pay_area)) break;

            $remoteVerifyUrl = 'https://mapi.alipay.com/gateway.do?service=notify_verify&partner=' . $configs['partner']
                . '&notify_id=' . urlencode($params['notify_id']);

            $responseTxt = $this->getHttpResponseGET($remoteVerifyUrl, $configs['cacert']);

            if (!preg_match("/true$/i", $responseTxt)) {
                PayLog::writeLogPay('请求失败');
                PayLog::writeLogPay($params,$pay_way.' sign error:');
            }


			if (preg_match("/true$/i", $responseTxt)) {
                $params['method'] = "notify";
                $params['pay_way'] = $pay_way;
                $params['user_pay_time'] = isset($params['gmt_payment']) ? $params['gmt_payment'] : $params['notify_time'];

                $trade = array_get($params,'out_trade_no');
                $trade = explode("_",$trade);
                if(empty($trade) || !isset($trade[1])){
                    $params['result'] = 'fail';
                    PayLog::writeLogPay($params,$pay_way);
                    die("fail");
                }

                if (isset($trade[3])) unset($trade[3]);
                if (isset($trade[4])) unset($trade[4]);
                $out_trade_no = implode('_', $trade);
                //记录支付log
                \DBConnection\Router::forceOnMaster();
                $pay = PayApiLog::where('out_trade_no', $out_trade_no)->orderBy('id', 'DESC')->first();
                \DBConnection\Router::resetToDefault();

                if (empty($pay)) {
                    die("fail");
                }

                $notify_result = App::make('Service\Pay')->notifySitePayResult($params, $pay);

                if($notify_result){
                    $params['result'] = 'success';
                    PayLog::writeLogPay($params,$pay_way);
                    die('success');
                }else {
                    $params['result'] = 'fail';
                    PayLog::writeLogPay($params,$pay_way);
                    die("fail");
                }

			} else {
                $params['result'] = 'fail';
                PayLog::writeLogPay($params,$pay_way);
                die("fail");
			}
        } catch (\Exception $e) {
            $params['result'] = 'fail';
            PayLog::writeLogPay($params, $pay_way);
            die("fail");
        }
    }

    /**
     * 获取支付的配置信息
     * @param string $params
     * @return bool
     */
    private static function getAppAliaPayConfig($pay)
    {
        $config = Config::get('pay');
        $config = $config[$pay->partner_id]['alipayapp'];

        if(empty($pay->id)|| empty($config)){
            return false;
        }
        return $config;
    }

    /**
     * 原始数组转换为支付宝所需字符串格式
     *
     * @param string $params
     * @param string $quote
     * @static
     * @access private
     * @return string
     */
    private static function _params2String($params, $quote = '"')
    {
        $legalStrings = array();
        ksort($params);
        reset($params);
        foreach($params as $key => $value)
        {
            $legalStrings[] = $key . '=' . $quote . $value . $quote;
        }

        $string = implode('&', $legalStrings);
        if (get_magic_quotes_gpc())
        {
            $string = stripslashes($string);
        }

        return $string;
    }

    /**
     * 添加签名
     *
     * @param string $string
     * @param string $quote
     * @access private
     * @return string
     */
    private function _addSign($string, $quote = '"', $configs)
    {
        $sign = '';
        $priKey = file_get_contents($configs['self_rsa_private_key']);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($string, $sign, $res);
        openssl_free_key($res);
        $sign = urlencode(base64_encode($sign));

        $string .= '&sign=' . $quote . $sign . $quote
            . '&sign_type=' . $quote . 'RSA' . $quote;

        return $string;
    }

    /**
     * 验证签名
     *
     * @param string $string
     * @param string $sign
     * @access private
     * @return bool
     */
    private function _verifySign($string, $sign, $pay_area)
    {
        $config = Config::get('pay');
        $configs = $config[$pay_area]['alipayapp'];

        $priKey = file_get_contents($configs['other_rsa_public_key']);
        $res = openssl_get_publickey($priKey);
        $result = (bool)openssl_verify($string, base64_decode($sign), $res);
        openssl_free_key($res);

        return $result;
    }

    function getHttpResponseGET($url,$cacert_url) {
    	$curl = curl_init($url);
    	curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    	curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    	curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
    	$responseText = curl_exec($curl);
    	curl_close($curl);

    	return $responseText;
    }
}
