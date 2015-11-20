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
use Service\Exception;
use App;

class Wxpay extends PayCreator
{
    public function createPayMethod($pay)
    {
        require_once(app_path() . '/libraries/ThirdPay/Wxpay.php');
        if (empty($pay->id)) {
            return false;
        }

        // 记住支付方式
        Cache::forever('payway_'.Auth::id(), 'wxpay');

        $money = round($pay->total_fee, 2);
        $subject = $pay->subject;
        $body = $pay->body;

        if ($money <= 0) {
            return false;
        }

        $paylog_model = new PayLog();
//        $out_trade_no = $paylog_model->_getBillInfo('wxpay', $pay->action, $money, $pay->action_id);
        $out_trade_no = $pay->out_trade_no."_".'wx';
        if (empty($out_trade_no) || !$out_trade_no) {
            return false;
        }

        $trade = explode('_', $out_trade_no);

        $data = [
            'body' => $subject,
            'out_trade_no' => $out_trade_no,
            'total_fee'=> $money,
            'order_no' => $pay->order_no,
            'action' => $trade[0],
            'object_id' => $pay->id,
            // 'sub_mch_id'=> $pay->id,
            'version' => 'service',
        ];

        $config = Config::get('pay');
        $config = $config[$pay->partner_id]['wxpay'];
        if (empty($config) || !$config) {
            return false;
        }

        $Wxpay = new \Wxpay($config);

        if (\UserAgent::is_mobile() || $pay->from=='app'){

            $jsApiParameters = $Wxpay->jsApiCall($data);
            if(!$jsApiParameters){
                return false;
            }
            $data['jsApiParameters'] = $jsApiParameters;

            return $data;

        } else{
            $code_url = $Wxpay->nativeDynamicQrcode($data);

            if(!$code_url){
                return false;
            }

            $data['code_url'] = $code_url;
            return \View::make('user.pay_wxpay')->with(['pay'=>$data]);
        }
    }

    /**
     * 微信支付通用通知接口
     */
    public function notifyPayMethod($pay_area, $is_app)
    {
        require_once(app_path() . '/libraries/ThirdPay/Wxpay.php');
        $config = Config::get('pay');
        $config = $config[$pay_area]['wxpay'];
        $pay_way = 'wxpay';

        $Wxpay = new \Wxpay($config);

        $data = $Wxpay->notifyUrl();

        App::make('Service\Pay')->writePayLog('PAY_RAW_LOG', array('url'=>\Request::url(), 'params'=>$data['params']));

        //支付成功
        if(isset($data['status']) && $data['status']){

            $params = $data['params'];
            $trade = explode("_",$params['out_trade_no']);
            $params['pay_way'] = $pay_way;
            $time = strtotime($params['time_end']);
            $params['user_pay_time'] = date("Y-m-d H:i:s",$time);

            if (isset($trade[3])) unset($trade[3]);
            $out_trade_no = implode('_', $trade);

            \DBConnection\Router::forceOnMaster();
            $pay = PayApiLog::where('out_trade_no', $out_trade_no)->orderBy('id', 'DESC')->first();
            \DBConnection\Router::resetToDefault();

            if(empty($trade) || !isset($trade[1])){
                $result = false;
            }else {
                $notify_result = App::make('Service\Pay')->notifySitePayResult($params, $pay);
                $result = $notify_result ? true : false;
            }

            PayLog::writeLogPay($params, $pay_way);
        } else {
            $result = false;
        }

        echo $result ? 'success' : 'fail';
        exit;
    }

    public function returnPayMethod($pay_area, $is_app)
    {

    }
}
