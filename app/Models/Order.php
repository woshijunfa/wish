<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Log;

class Order extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'order';


    protected $primaryKey = 'order_id';

    protected $dateFormat = 'U';

    protected $fillable = [
                            'order_id',
                            'subject',
                            'total_fee',
                            'service_fee',
                            'partner_fee',
                            'pay_time',
                            'user_id',
                            'partner_id',
                            'order_status',
                            'created_at',
                            'updated_at',
                            'remark'
                            ];    
    

    //创建订单
    public static function createOrder($info)
    {
        if (empty($info)) return false;
        $info['order_dates'] =  implode(',', $info['order_dates']);
        $result = self::create($info);
        return empty($result) ? $result : $result->order_id;
    }

    public static function getOrderInfoById($orderId)
    {
        if (empty($orderId)) return false;

        $result = self::where('order_id',$orderId)->first();
        return empty($result) ? $result : $result->toArray();
    }

}

