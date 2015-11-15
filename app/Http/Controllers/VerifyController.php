<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\GlobalDef;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Response;
use Session;
use Log;
use App\Models\User;

class VerifyController extends BaseController
{
    const PHONE_MSG_LOCK_PREFIX = 'msg_send_';

    //获取验证码
    public function getimg(Request $request)
    {
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
        Log::info('generate veriry code:'.$authnum);
        header("Content-type: image/png", true, 200);
        imagepng($aimg); //生成png格式
        imagedestroy($aimg);
        $response = Response('', 200)->header('Content-Type', 'image/png');
        return $response;
    }

    public function checkimg()
    {
        $code = Input::get('verifycode');
        if (empty($code)) return $this->returnJsonResult(1,'需要图片验证码');

        $isRight = Utility::checkImgCode($code,false); 
        return $this->returnJsonResult(0,'',$isRight);
    }

    public function sendPhoneCode(Request $request)
    {
        //获取发送类型
        $action = Input::get('action');

        //注册方法
        $registAction = array();
        $registAction['regist'] = 'sendRegistPhoneCode';
        $registAction['resetloginpass'] = 'sendResetLoginPassPhoneCode';

        //调用方法
        if (empty($registAction[$action])) return $this->returnJsonResult(1,'action不存在');

        return $this->$registAction[$action]($request);
    }

    private function sendRegistPhoneCode(Request $request)
    {
        $code = Input::get('verifycode');
        $mobile = Input::get('mobile');
        
        //交验参数
        if (empty($mobile)) return $this->returnJsonResult(1,'手机号码为空');

        //校验手机格式
        $isok = Utility::checkFormatMobileNum($mobile);
        if (!$isok) return $this->returnJsonResult(2,'不是手机号码');

        if (empty($code)) return $this->returnJsonResult(3,'需要图片验证码');

        //校验验证码是否正确
        $isRight = Utility::checkImgCode($code); 
        if(!$isRight) return $this->returnJsonResult(3,'验证码错误');

        //校验是否注册
        $isExist = User::isAccountExist($mobile);
        if ($isExist) return $this->returnJsonResult(4,'手机号码已注册');

        //发送手机验证码
        $isok = Utility::sendRegistPhoneMsg($mobile);
        if ($isok !== true) return $this->returnJsonResult(5,$isok['message']);

        return $this->returnJsonResult(0,'',true);
    }

    private function sendResetLoginPassPhoneCode(Request $request)
    {
        $code = Input::get('verifycode');
        $mobile = Input::get('mobile');

        //交验参数
        if (empty($mobile)) return $this->returnJsonResult(1,'手机号码为空');

        //校验手机格式
        $isok = Utility::checkFormatMobileNum($mobile);
        if (!$isok) return $this->returnJsonResult(2,'不是手机号码');

        if (empty($code)) return $this->returnJsonResult(3,'需要图片验证码');

        //校验验证码是否正确
        $isRight = Utility::checkImgCode($code); 
        if(!$isRight) return $this->returnJsonResult(3,'验证码错误');

        //校验是否注册
        $isExist = User::isAccountExist($mobile);
        if (!$isExist) return $this->returnJsonResult(4,'手机号码未注册');

        //发送手机验证码
        $isok = Utility::sendResetLoginPassPhoneMsg($mobile);
        if ($isok !== true) return $this->returnJsonResult(5,$isok['message']);

        return $this->returnJsonResult(0,'',$isok);
    }

}
