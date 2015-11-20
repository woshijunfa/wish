<?php
/**
 * Created by PhpStorm.
 * User: liqiqi
 * Date: 2015/6/5
 * Time: 11:12
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

class WapAlipay extends PayCreator
{
    public function createPayMethod($pay, $from='')
    {
        $config = Config::get('pay');
        $config = $config[$pay->partner_id]['alipaywap'];

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
        //$out_trade_no = $paylog_model->_getBillInfo('alipay', $pay->action, $money, $pay->action_id);
        $create_time = $pay->create_time;
        $create_time = date_create($create_time);
        $now = date_create();
        $minutes = date_diff($now, $create_time);
        $no = floor((($minutes->format("%i"))/30)+1);
        $out_trade_no = $pay->out_trade_no.'_al_'.$no;

        //$out_trade_no = $pay->out_trade_no.'_alm';

        \DBConnection\Router::forceOnMaster();
        $pay_log_item = PayLog::where('out_trade_no', $out_trade_no)->first();
        \DBConnection\Router::resetToDefault();
        if (empty($pay_log_item)) {
            $bill_log = array(
                'action_id'      =>$pay->action_id,
                'out_trade_no' =>$out_trade_no,
                'pay_way'      => 'wapalipay',
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

        require_once( base_path().'/alipaywap/lib/alipay_submit.class.php');
        /**************************请求参数**************************/
        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = $config['service_notify_url'];;
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = $config['service_return_url'];
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        //商户订单号
        //$out_trade_no = $_POST['WIDout_trade_no'];
        //商户网站订单系统中唯一订单号，必填
        //订单名称
        //$subject = $_POST['WIDsubject'];
        //必填
        //付款金额
        $total_fee = $money;
        //必填
        //商品展示地址
        $show_url = "";
        //必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
        //订单描述
        //$body = $_POST['WIDbody'];
        //选填
        //超时时间
        $it_b_pay = '30m';
        //选填
        //钱包token
        $extern_token = '';
        //选填

        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "alipay.wap.create.direct.pay.by.user",
            "partner" => trim($config['partner']),
            "seller_id" => trim($config['partner']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "show_url"	=> $show_url,
            "body"	=> $body,
            "it_b_pay"	=> $it_b_pay,
            "extern_token"	=> $extern_token,
            "_input_charset"	=> trim(strtolower($config['input_charset']))
        );

        //建立请求
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
        $config = $config[$pay_area]['alipaywap'];
        $pay_way = 'wapalipay';

        require_once( base_path().'/alipaywap/lib/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($config);
        $verify_result = $alipayNotify->verifyNotify();

        $params['method'] = "notify";
        $params['pay_way'] = $pay_way;
        if(isset($params['notify_time']) && isset($params['out_trade_no'])){
            $params['user_pay_time'] = isset($params['gmt_payment']) ? $params['gmt_payment'] : $params['notify_time'];
        }else{
            die("fail");
        }

        if (!$verify_result) {
            $reset_pay_area = '5998757370';
            $config = Config::get('pay')[$reset_pay_area]['alipaywap'];
            Config::set('pay.2833826846', Config::get('pay.5998757370'));
            $alipayNotify = new \AlipayNotify($config);
            $verify_result = $alipayNotify->verifyNotify();
        }

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
        $config = $config[$pay_area]['alipaywap'];
        $pay_way = 'wapalipay';

        require_once( base_path().'/alipaywap/lib/alipay_notify.class.php');

        $params = Input::all();

        App::make('Service\Pay')->writePayLog('PAY_RAW_LOG', array('url'=>Request::url(), 'params'=>$params));

        $params['method'] = "return";
        $params['pay_way'] = $pay_way;
        if(isset($params['notify_time']) && isset($params['out_trade_no'])){
            $params['user_pay_time'] = isset($params['gmt_payment']) ? $params['gmt_payment'] : $params['notify_time'];
        }else{
            return Redirect::to("/i/user/home");
        }


        //计算得出通知验证结果
        $alipayNotify  = new \AlipayNotify($config);
        $verify_result = $alipayNotify->verifyReturn();

        if (!$verify_result) {
            $reset_pay_area = '5998757370';
            $config = Config::get('pay')[$reset_pay_area]['alipaywap'];
            Config::set('pay.2833826846', Config::get('pay.5998757370'));
            $alipayNotify  = new \AlipayNotify($config);
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
            //判断异步通知是否已经通知成功
            $trade = array_get($params,'out_trade_no');
            $trade = explode("_",$trade);

            if (isset($trade[3])) unset($trade[3]);
            if (isset($trade[4])) unset($trade[4]);
            $out_trade_no = implode('_', $trade);

            \DBConnection\Router::forceOnMaster();
            $pay = PayApiLog::where('out_trade_no', $out_trade_no)->orderBy('id', 'DESC')->first();
            \DBConnection\Router::resetToDefault();
            //$bill = Bill::find($pay->$action_id);
            if($pay->pay_status==1 && $pay->notify_status==1){
                $result = true;
                $params['result'] = 'success';
                PayLog::writeLogPay($params,$pay_way);
            } else {
                $result = false;
                $params['result'] = 'fail';
                PayLog::writeLogPay($params,$pay_way);
            }

        }

        $is_mobile = \UserAgent::is_mobile();
        if ($result) {
            if(Input::get('from') == 'app'){
                return Redirect::to("/api/v1/home");
            }
            if($is_mobile){

                return View::make('mobile.user.pay_success')->with(array('url' => $pay->site_back_url));
                //return \Redirect::to($pay->site_back_url);
            }
            return View::make('user.pay_success')->with(array('url' => $pay->site_back_url));
        } else {

            $fail_back_url = isset($fail_back_url) ? $fail_back_url : Request::root();
            return Utility::ToMessage('支付失败', $fail_back_url, 'fail');
        }
    }
}
