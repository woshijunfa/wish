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
use Input;
use PayLog;
use PayApiLog;
use Redirect;
use View;
use App;
use Request;
use Utility;
use Service\Exception;

class Alipay extends PayCreator
{
    public function createPayMethod($pay, $from='')
    {
        $config = Config::get('pay');
        $config = $config[$pay->partner_id]['alipay'];

        if(empty($pay->id)|| empty($config)){
            return false;
        }

        // 记住支付方式
        Cache::forever('payway_'.Auth::id(), 'alipay');

        $money = round($pay->total_fee, 2);
        $subject = $pay->subject;
        $body = $pay->body;

        if($money<=0){
            return false;
        }

        $paylog_model = new PayLog();
//        $out_trade_no = $paylog_model->_getBillInfo('alipay', $pay->action, $money, $pay->action_id);
        $create_time = $pay->create_time;
        $create_time = date_create($create_time);
        $now = date_create();
        $minutes = date_diff($now, $create_time);
        $no = floor((($minutes->format("%i"))/30)+1);
        $out_trade_no = $pay->out_trade_no.'_al_'.$no;

        \DBConnection\Router::forceOnMaster();
        $pay_log_item = PayLog::where('out_trade_no', $out_trade_no)->first();
        \DBConnection\Router::resetToDefault();
        if (empty($pay_log_item)) {
            $bill_log = array(
                'action_id'      =>$pay->action_id,
                'out_trade_no' =>$out_trade_no,
                'pay_way'      => 'alipay',
                'ip'           => Utility::getClientIp(),
                'action'       =>$pay->action,
                'money'        => $pay->total_fee,
                'pay_method'  => array_get(PayLog::$dict_pay_method, 'alipay', 'undefined'),
                'partner'      => $pay->partner_id,
            );

            $pay_log = new PayLog($bill_log);
            $pay_log->save();
        }

        if(empty($out_trade_no) || !$out_trade_no){
            return false;
        }
//        //由于支付宝生成的out_trade_no有30分钟有效期，此处容易起冲突，所以需要将原有pay_api_log中相同out_trade_no记录修改掉
//        $old_pay_api_log = PayApiLog::where('out_trade_no', $out_trade_no)->first();
//        if (!empty($old_pay_api_log)) {
//            $old_pay_api_log->out_trade_no = 'tmp'.'_'.$old_pay_api_log->action_id.'_'.rand(10000, 99999);
//            $old_pay_api_log->save();
//        }

        require_once( base_path().'/alipay/lib/alipay_submit.class.php');
        /**************************请求参数**************************/
        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = $config['service_notify_url'];
        //需http://格式的完整路径，不能加?id=123这类自定义参数


        //页面跳转同步通知页面路径
        $return_url = $config['service_return_url'];

        if($pay->from == 'app'){
            $return_url .= '?from=app';
        }
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //卖家支付宝帐户
        $seller_email = $config['my_alipay_acc'];
        //必填

        //商户网站订单系统中唯一订单号，必填
        //订单名称

        //必填
        //付款金额
        $total_fee = $money;
        $extra_common_param = $pay->id;

        $show_url = '';
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
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
            "partner" => trim($config['partner']),
            "payment_type"  => $payment_type,
            "notify_url"    => $notify_url,
            "return_url"    => $return_url,
            "body" => $body,
            "seller_email"  => $seller_email,
            "extra_common_param" => $extra_common_param,
            "out_trade_no"  => $out_trade_no,
            "subject"   => $subject,
            "it_b_pay" => "30m",
            "total_fee" => $total_fee,
            "_input_charset"    => trim(strtolower($config['input_charset']))
        );
        $alipaySubmit = new \AlipaySubmit($config);
        $str = $alipaySubmit->buildRequestForm($parameter,"get", "确认");

        $device = \UserAgent::is_mobile() ? 'h5' : 'web';
        $str = $paylog_model->getPayFormHtml($device, $str);
        return $str;
    }

    /**
     * 支付宝异步通知，
     */
    public function notifyPayMethod($pay_area, $is_app)
    {
        $params = Input::all();

        App::make('Service\Pay')->writePayLog('PAY_RAW_LOG', array('url'=>Request::url(), 'params'=>$params));

        $result = false;
        $config = Config::get('pay');
        $config = $config[$pay_area]['alipay'];
        $pay_way = 'alipay';

        require_once( base_path().'/alipay/lib/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($config);
        $verify_result = $alipayNotify->verifyNotify();

        if (!$verify_result) {
            $reset_pay_area = '2833826846';
            $config = Config::get('pay')[$reset_pay_area]['alipay'];
            Config::set('pay.5998757370', Config::get('pay.2833826846'));
            $alipayNotify = new \AlipayNotify($config);
            $verify_result = $alipayNotify->verifyNotify();
        }

        $params['method'] = "notify";
        $params['pay_way'] = $pay_way;
        $params['user_pay_time'] = isset($params['gmt_payment']) ? $params['gmt_payment'] : $params['notify_time'];

        if($verify_result) {
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
    }

    public function returnPayMethod($pay_area, $is_app) {
        $result = false;
        $config = Config::get('pay');
        $config = $config[$pay_area]['alipay'];
        $pay_way = 'alipay';

        require_once( base_path().'/alipay/lib/alipay_notify.class.php');

        $params = Input::all();

        if (!isset($params['notify_time'])) {
            return View::make('user.pay_success')->with(array('url' => '/user/home'));
        }

        App::make('Service\Pay')->writePayLog('PAY_RAW_LOG', array('url'=>Request::url(), 'params'=>$params));

        $params['method'] = "return";
        $params['pay_way'] = $pay_way;
        $params['user_pay_time'] = isset($params['gmt_payment']) ? $params['gmt_payment'] : $params['notify_time'];

        //计算得出通知验证结果
        $alipayNotify  = new \AlipayNotify($config);
        $verify_result = $alipayNotify->verifyReturn();

        if (!$verify_result) {
            $reset_pay_area = '2833826846';
            $config = Config::get('pay')[$reset_pay_area]['alipay'];
            Config::set('pay.5998757370', Config::get('pay.2833826846'));
            $alipayNotify = new \AlipayNotify($config);
            $verify_result = $alipayNotify->verifyReturn();
        }


        if($verify_result) {//验证成功
            $trade = array_get($params,'out_trade_no');
            $trade = explode("_",$trade);

            if (isset($trade[3])) unset($trade[3]);
            if (isset($trade[4])) unset($trade[4]);
            $out_trade_no = implode('_', $trade);

            \DBConnection\Router::forceOnMaster();
            $pay = PayApiLog::where('out_trade_no', $out_trade_no)->orderBy('id', 'DESC')->first();
            \DBConnection\Router::resetToDefault();

            //由于无法确定支付来源，所以只能跳转到首页
            if (empty($pay)) {
                $params['result'] = 'fail';
                PayLog::writeLogPay($params,$pay_way);
                return Utility::ToMessage('支付失败', Request::root(), 'fail');
            }

            $fail_back_url = '/pay/payment/'.$pay->pay_no;

            if(empty($trade) || !isset($trade[1])){
                $params['result'] = 'fail';
                PayLog::writeLogPay($params,$pay_way);
                return Utility::ToMessage('支付失败', $fail_back_url, 'fail');
            }

            $notify_result = App::make('Service\Pay')->notifySitePayResult($params, $pay);

            if($notify_result){
                $result = true;
                $params['result'] = 'success';
                PayLog::writeLogPay($params,$pay_way);
            }else {
                $result = false;
                $params['result'] = 'fail';
                PayLog::writeLogPay($params,$pay_way);
            }
        }else {
            $result = false;
            $params['result'] = 'fail';
            PayLog::writeLogPay($params,$pay_way);
        }

        $is_mobile = \UserAgent::is_mobile();
        if ($result) {
            if(Input::get('from') == 'app'){
                return Redirect::to("/api/v1/home");
            }
            if($is_mobile){
                return View::make('mobile.user.pay_success')->with(array('url' => $pay->site_back_url));
            }
            // 输出支付成功页面
            return View::make('user.pay_success')->with(array('url' => $pay->site_back_url));
        } else {
            $fail_back_url = isset($fail_back_url) ? $fail_back_url : Request::root();
            return Utility::ToMessage('支付失败', $fail_back_url, 'fail');
        }
    }
}
