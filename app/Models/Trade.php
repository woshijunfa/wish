<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Log;

class Trade extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'trade';


    protected $primaryKey = 'trade_no';

    protected $dateFormat = 'U';

    protected $fillable = [
                            'trade_no',
                            'ch_id',
                            'order_id',
                            'status',
                            'channel',
                            'amount',
                            'created_at',
                            'updated_at',
                            'remark'
                            ];    
    

    //创建订单
    public static function createCharge($info)
    {
        if (empty($info)) return false;
        $result = self::create($info);

        //非自增主键，直接返回
        return empty($result) ? $result : $info['trade_no'];
    }

    public static function getTradeByTradeNo($tradeNo)
    {
        if (empty($tradeNo)) return false;
        return self::where('trade_no',$tradeNo)->first();
    }

}

