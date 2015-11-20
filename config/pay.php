<?php

return [

    'alipay'=>[

            //合作者身份id
            'partner'       => 'xxxxx',

            //安全检验码，以数字和字母组成的32位字符
            'key'           => 'xxxxx',

            //签名方式 不需修改
            'sign_type'     => strtoupper('MD5'),

            //字符编码格式 目前支持 gbk 或 utf-8
            'input_charset' => strtolower('utf-8'),
            
            //ca证书路径地址，用于curl中ssl校验
            //请保证cacert.pem文件在当前文件夹目录中
            'cacert'        => base_path().'/alipay/cacert.pem',

            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            'transport'     => 'http',

            //收款支付宝账号
            'seller_email'  => 'chenjunfa1988@yeah.net',

            'my_order_pre'  => 'csl_',
            'my_alipay_acc' => "myaccount@yeah.net",
    ],

    'wxpay'=>[
            // //=======【基本信息设置】=====================================
            // //微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
            // 'APPID' => 'wxxxxxxxxxxx',
            // //受理商ID，身份标识
            // 'MCHID' => '12345678',
            // //子账号
            // 'SUB_MCH_ID' => '12345678777',
            // //商户支付密钥Key。审核通过后，在微信发送的邮件中查看
            // 'KEY' => '345345345345dfgsfgs',
            // //JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
            // //'APPSECRET' => 'c2681eb85520d18f870ee580fcf3aa22',
            // 'payee_accout'   => 'dfgsdfgdfg',
            // //=======【JSAPI路径设置】===================================
            // //获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
            // //    'JS_API_CALL_URL' => 'http://' . $_SERVER['SERVER_NAME'] . '/i/pay/jsApiCallWxpay/beijing',
            // 'JS_API_CALL_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/i/pay/jsApiCallWxpay/beijing',

            // //=======【证书路径设置】=====================================
            // //证书路径,注意应该填写绝对路径
            // 'SSLCERT_PATH' => base_path() . '/libraries/ThirdPay/Wxpay/WxPayPubHelper/cacert/apiclient_cert.pem',
            // 'SSLKEY_PATH' => base_path() . '/libraries/ThirdPay/Wxpay/WxPayPubHelper/cacert/apiclient_key.pem',

            // //=======【curl超时设置】===================================
            // //本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
            // 'CURL_TIMEOUT' => 30,

            // 'SERVICE_JS_API_CALL_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/i/pay/jsApiCallWxpay/2833826846',
            // 'SERVICE_NOTIFY_URL' => 'http://' . $_SERVER['HTTP_HOST'] . '/pay/notify/wxpay/2833826846',
    ],

];
