<?php
/**
 * Created by PhpStorm.
 * User: jianyong
 * Date: 14-7-26
 * Time: 15:20
 */
namespace App\Models;
class GlobalDef{

    const PHONE_VERIFY_CODE_SEND_DURATION_SECOND    = 60;               //同一类别的验证码，发送间隔至少60秒
    const PHONE_VERIFY_CODE_DURATION_MINUTE         = 30;               //手机验证码30分钟有效

    const REGIST_MOBILE_VERIFY_CODE                 = '验证码为%s，仅用于注册wish账号, 切勿泄漏';
    const PHONE_RESET_LOGIN_PASSWORD_MESSAGE        = '验证码为%s，仅用于找回wish账号密码, 切勿泄漏';
    const PHONE_VERIFY_CODE_PREIFIX                 = 'phone_verify_code_';


    const SERVICE_FEE_PERCENT                       = 10;       //单位百分之1




	const ORDER_EXPIRE_MINUTE 		    = 10; 	//订单十分钟，过期





    //订单状态码
    const   ORDER_STATUS_INIT                           =   0;      //定单创建
    const   ORDER_STATUS_PAYED                          =   1;      //用户支付
    const   ORDER_STATUS_CONFIRM                        =   2;      //导游确认
    const   ORDER_STATUS_FINISH                         =   3;      //订单完成
    const   ORDER_STATUS_CANCEL                         =   4;      //订单取消
    const   ORDER_STATUS_APPLY_CANCEL                   =   5;      //订单申请取消（在确认之后，finish之前取消）
    const   ORDER_STATUS_APPEAL                         =   6;      //导游不同意取消，进入申诉状态
    const   ORDER_STATUS_APPEAL_FINISH                  =   7;      //申诉方式结束订单
    const   ORDER_STATUS_JUST_PAYED                     =   8;      //只是支付成功，但是行程单被抢约


    //支付通道状态吗
    const   PAY_CHANNEL_ALIPAY_PC_DIRECT                =   'alipay_pc_direct';         //支付宝PC直接支付
    const   PAY_CHANNEL_UPACP_PC                        =   'upacp_pc';                 //PC的银联支付

    const   PAY_CHANNEL_ALIPAY_WAP                      =   'alipay_wap';               //支付宝wap网页支付
    const   PAY_CHANNEL_UPACP_WAP                       =   'upacp_wap';                //银联 WAP
    const   PAY_CHANNEL_WX                              =   'wx';                       //微信支付




    //全局标识符
    const REGIST_PHONE_CHECK_NUM_FAILE_TIMES = 'regist_phonecheck_failed_count';            //注册账号是发送手机验证码次数是否超过三次
    const REGIST_PHONE_CHECK_NUM_FAILE_LINE = 3;                                            //注册的时候手机号码教研超过三次就需要验证码
    const PHONE_VERIFY_CODE = 'phone_verify_code';
    const SESSION_REGIST_CHECKD_MOBILE = 'regist_checked_mobile';                           //注册时发送短信验证是否收到

    //手机验证码相关
    const PHONE_VERIFY_CODE_TRY_ERR_PREIFIX             = 'phone_verify_code_tryerr_';
    const LOGIN_MOBILE_VERIFY_CODE = '验证码为%s，如非本人操作，请及时修改密码，如有问题请致电客服4000990707。';
    const PHONE_LOCK_ACCOUNT_MESSAGE = '同学您好，由于您的账号存在异常操作，该账号已被锁定。可在 http://dwz.cn/27e3um 找回密码或等待系统24小时后自动解锁，如非本人操作请致电4000990707【请勿透露账号密码及验证码信息给他人，以免造成损失】';
    const PHONE_MESSAGE_DAY_TOO_MATCH = '今天该手机号发送过多，请24小时后再试';     //
    const PHONE_REGIST_VERIFY_CODE_NEED_IMGCODE     = 3;               //注册的时候3次验证码错误则要求输入图片验证码
    const PHONE_MSG_REGIST_ERROR_SESSION_PREFIX = 'reg_phone_code_err';
    const PHONE_CODE_MAX_TRYTIMES               = 10;                   //发送的手机号最大的错误重试次数，超过则失效

    //重新设置密码的sessionkeys
    const RESET_PASS_PHONE = 'resetPassPhone';
    const RESET_PASS_STEP = 'resetpass-step';
    const RESET_PASS_IS_PASS = 'resetpass-ispass';

    //重新设置登录密码的步骤
    const RESET_PASS_STEP_SET_ACCOUNT = 'reset_pass_set_account';       //第一步输入账号
    const RESET_PASS_STEP_SELECT_METHOD = 'reset_pass_select_method';   //第二步选择途径
    const RESET_PASS_STEP_PHONE_VERIFY = 'reset_pass_phone_verify';     //第三步验证手机号
    const RESET_PASS_STEP_SET_NEWPASS = 'reset_pass_set_newpass';       //第四部设置新密码
    const RESET_PASS_STEP_SUCCESS = 'reset_pass_success';               //最后成功页面

    const PASSPORT_TOKEN = 'passport_token';
    const PASSPORT_TOKEN_EXPIRE = 'passport_token_expire';

    //页面入口来源
    const FROM_SHIISHENG                    = 'cadet';                  //实习生的from
    const FROM_SHIISHENG_USER_TYPE          = 5;                        //实习生的用户类型

    //服务器消息列表
    const MESSAGE_RESET_LOGIN_PASSWORD      = 'message_reset_login_password';       //修改登录密码的消息

}

