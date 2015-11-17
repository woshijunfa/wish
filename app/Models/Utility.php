<?php
/**
 * Created by PhpStorm.
 * User: jianyong
 * Date: 14-7-26
 * Time: 15:20
 */
namespace App\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Log;
use Input;
use Config;
use Session;
use Cache;
use App;
use Cookie;

class Utility extends Eloquent {

    const VERIFY_IMG_CODE = 'img_code';


    //php生成GUID
    public static function getGuid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));

        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }


    //生成验证码
    public static function verifyImg() {

        $width = Input::get('width', 90);
        $heigth = Input::get('height', 40);

        $img_height= min($width, 100);//先定义图片的长、宽
        $img_width= min($heigth, 40);

        $authnum='';
        //生产验证码字符
        $ychar="2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,J,K,L,M,N,P,Q,R,S,T,U,V,W,X,Y,Z";
        $list=explode(",",$ychar);
        for($i=0;$i<4;$i++){
            $randnum=rand(0,31);
            $authnum.=$list[$randnum];
        }

        //把验证码字符保存到session
        $aimg = imagecreate($img_height,$img_width);        //生成图片
        imagecolorallocate($aimg, 255,255,255);                //图片底色，ImageColorAllocate第1次定义颜色PHP就认为是底色了
        $black = imagecolorallocate($aimg, 0,0,0);            //定义需要的黑色

        $noise = array('@', '#', '~', '*', '?', '<', '>', '(', ')');
        shuffle($noise);
        $noise_len = count($noise);
        $seed = rand(0, $noise_len - 1);

        for ($i=1; $i<=50; $i++) {
            imagestring($aimg, mt_rand(2,3), mt_rand(1,$img_height),mt_rand(1,$img_width), $noise[$seed],imagecolorallocate($aimg,mt_rand(180,255),mt_rand(180,255),mt_rand(200,255)));
            $seed = ($i*($seed+1)) % $noise_len;
        }

        //为了区别于背景，这里的颜色不超过200，上面的不小于200
        for ($i=0;$i<strlen($authnum);$i++){
            imagestring($aimg, mt_rand(3,5), $i*$img_height/4+mt_rand(2,7), mt_rand(1,$img_width/2-2),
                $authnum[$i],imagecolorallocate($aimg,mt_rand(100,200),mt_rand(10,80),mt_rand(0,200)));
        }
        imagerectangle($aimg,0,0,$img_height-1,$img_width-1,$black);//画一个矩形

        session([Utility::VERIFY_IMG_CODE => $authnum]);
        Session::save();
        header("Content-type: image/png", true, 200);
        imagepng($aimg); //生成png格式
        imagedestroy($aimg);
        $response = Response::make('', 200);
        $response->header('Content-Type', 'image/png');
        return $response;
    }

    //获取length位随机数字/字母
    public static function getRandomString($length,$isNumberic)
    {
        $ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,J,K,L,M,N,P,Q,R,S,T,U,V,W,X,Y,Z";
        if ($isNumberic) $ychar="0,1,2,3,4,5,6,7,8,9";

        $list=explode(",",$ychar);
        $rtnstr = '';
        for($i=0;$i<$length;$i++){
            $randnum=rand(0,count($list)-1);
            $rtnstr.=$list[$randnum];
        }
        return $rtnstr;
    }


    //给手机号码发短信
    public static function sendPhoneMsg($phoneNum,$content,$action=0,$verifycode = '')
    {
        if (empty($phoneNum) || empty($content)) return false;
        Cache::put(GlobalDef::PHONE_VERIFY_CODE_PREIFIX . $phoneNum,$verifycode,GlobalDef::PHONE_VERIFY_CODE_DURATION_MINUTE);

        //打印断点日志
        Log::info('send_phone_msg mobile=' . $phoneNum . ' content = '. $content);
        return true;
    }

    public static function sendRegistPhoneMsg($phoneNum)
    {
        if (empty($phoneNum)) return false;

        $content = GlobalDef::REGIST_MOBILE_VERIFY_CODE;
        $code = self::getRandomString(6,true);
        $content = sprintf($content,$code);

        return self::sendPhoneMsg($phoneNum,$content,1,$code);
    }

    public static function sendResetLoginPassPhoneMsg($phoneNum)
    {
        if (empty($phoneNum)) return false;
        $content = GlobalDef::PHONE_RESET_LOGIN_PASSWORD_MESSAGE;
        $code = self::getRandomString(6,true);
        $content = sprintf($content,$code);

        return self::sendPhoneMsg($phoneNum,$content,2,$code);
    }


    public static function tryCheckPhoneCode($mobileNum,$code)
    {
        if (empty($mobileNum) || empty($code)) return false;
        $fullToken = GlobalDef::PHONE_VERIFY_CODE_PREIFIX . $mobileNum;
        $value = Cache::get($fullToken);

        return $value == $code;
    }

    //校验手机密码是否正确
    public static function checkPhoneCode($mobileNum,$code)
    {
        $isok = self::tryCheckPhoneCode($mobileNum,$code);
        if ($isok) Cache::forget(GlobalDef::PHONE_VERIFY_CODE_PREIFIX . $mobileNum);

        return $isok;
    }

    //校验手机号是否符合规则
    static public function checkFormatMobileNum($mobilenum)
    {
        $phonereg = '/^((1[0-9]{2})|159|153)+\d{8}$/';
        $result = preg_match($phonereg, $mobilenum);
        return !empty($result);
    }

    static public function checkImgCode($imgcode,$isdel=true)
    {
        //验证码验证成功后失效
        $result = strtolower($imgcode) == strtolower(session(Utility::VERIFY_IMG_CODE));
        if ($result && $isdel) {
            Session::forget(Utility::VERIFY_IMG_CODE);
            Session::save();
        }
        return $result;
    }

    static public function globalLock($key,$ttlSeconds)
    {
        if (empty($key) || empty($ttlSeconds)) return false;

        $redis = Redis::connection();
        $status = $redis->set($key,'1');
        if ('OK' != $status->getPayload()) return false;
        $redis->expire($key,$ttlSeconds);

        return true;
    }

    static public function isGlobalLocked($key)
    {
        if (empty($key)) return false;

        $redis = Redis::connection();
        $islock = $redis->get($key);

        return !empty($islock);
    }

    static public function delGlobalLock($key)
    {
        if (empty($key)) return false;

        $redis = Redis::connection();
        $islock = $redis->del($key);

        return true;
    }


    /* getClientIp
     * 获取客户端Ip地址
     *
     * @static
     * @return string
     */
    public static function getClientIp()
    {
        if (getenv('HTTP_REMOTEIP')) {
            $onlineip = getenv('HTTP_REMOTEIP');
        } elseif(getenv('HTTP_CLIENT_IP')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $onlineip = getenv('REMOTE_ADDR');
        } else {
            $onlineip = '127.0.0.1';
        }

        return $onlineip;
    }

    /**
     * curl工具方法
     * @param $url 请求地址
     * @param string $requestType 请求方式 post 或 get
     * @param array $data post 请求数据
     * @param int $timeout 请求超时
     * @return mixed
     */
    public static function curlRequest($url, $requestType = "get", $data = array(), $timeout = 3)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if (strtolower($requestType) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function LogException($e)
    {
        return Log::info($e);
        return app("App\Services\ExceptionMailer")->addException($e);
    }

    //根据ip获取地域
    public static function getAreaByIp($ip)
    {
        if (empty($ip)) return false;

        try 
        {
            $area = Cache::get('ip_' . $ip);
            if (!empty($area)) return $area;


            $obj = new \App\Services\IPService();
            $area = $obj->find($ip);

            if (empty($area) || !is_array($area) || count($area) < 3) return false;

            $areastr = '';
            $count = 0;
            foreach ($area as $value) {
                if (!empty($value) && $count < 3) 
                {
                    $areastr .= $value;
                    $count++;
                }
            }

            if(!empty($areastr)) Cache::put('ip_'. $ip,$areastr,60);
            return empty($areastr) ? false : $areastr;
        } 
        catch (\Exception $e) 
        {
            return false;
        }

        return false;
    } 

    public static function getLoginArea()
    {
        $ip = self::getClientIp();
        return self::getAreaByIp($ip);
    }

    public static function serialArray($data)
    {
        if (!is_array($data)) return '';
        
        $rtnstr = '';
        foreach ($data as $key => $value) 
        {
            $rtnstr .= $key.":".$value." ";
        }
        return $rtnstr;
    } 

    public static function validDate($date)
    {
        if (!is_string($date)) return false;

        $dateReg = '/^\d4\d2\d2$/';
        return preg_match($phonereg, $mobilenum);
    }

}
