<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Utility;
use App\Models\GlobalDef;
use Auth;
//use App\Models\Test;
use App\Models\Calendar;
use App\Services\PayService;
use App\Services\CalendarService;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orderInfo = Order::getOrderInfoById(1);
        $dates = explode(',',$orderInfo['order_dates']);
        //更新行程单
        Calendar::orderCalendar($orderInfo['user_id'],$orderInfo['partner_id'],$dates,$orderInfo['order_id']);
die;
        var_dump();

        var_dump($orderInfo);
        $charge = PayService::getPingppObject(GlobalDef::PAY_CHANNEL_ALIPAY_PC_DIRECT,$orderInfo);
        $str = json_decode(sprintf('%s',$charge),true);
        var_dump($str);
die;
die;
die;
        var_dump(CalendarService::lockUser(2,1,['2015-11-02'],time()+10,1));
        var_dump(CalendarService::lockUser(2,1,['2015-11-02'],time()+10,2));
        
        PayService::alipaySign([]);

        var_dump($result);
        var_dump(CalendarService::insertCals(2,[]));
        var_dump(Calendar::getCalByDates(2,'2015-10-13'));
        var_dump(CalendarService::getUserCalendarMonth(1,'2015-10'));
        var_dump(User::resetUserPasswordByAccount('13021705991','123456'));
        $array = [
            'mobile'    => '13521705999',
            'password'  => md5('123456'),
            'nickname'  => '13521705999'
        ];
        $dd = \App\Models\Test::create(['test'=>'df']);
        $dd = NULL;
        var_dump(empty($dd) ? $dd :$dd->id);
        //
    }
}

