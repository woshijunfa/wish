<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Log;
use DB;

class Calendar extends Model
{

    /** 
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'calendar';


    protected $primaryKey = 'callendar_id';

    protected $dateFormat = 'U';

    protected $fillable = [
                            'calendar_id',
                            'user_id',
                            'order_id',
                            'date',
                            'month',
                            'price',
                            'pre_status',
                            'status',
                            'remark',
                            'created_at',
                            'updated_at'
                            ];    
    

    public static function getByMonth($userId,$month)
    {
        if (empty($userId) || empty($month)) return false;

        $info = self::where('user_id',$userId)
                    ->where('month',$month)
                    ->get();

        return empty($info) ? false : $info->toArray();                    
    }

    public static function insertCals($calInfos)
    {
        if (empty($calInfos)) return false;
        return self::insert($calInfos);
    }

    public static function getCalByDates($userId,$dates)
    {
        if (empty($userId) || empty($dates)) return false;
        if (!is_array($dates)) $dates = array($dates);

        $result = self::where('user_id',$userId)
                  ->whereIn('date',$dates)
                  ->get();

        return empty($result) ? false : $result->toArray();                    
    }


    //用户下单后更改行程单
    //$cusId            客人的用户id
    //$busId            导游的用户id
    //$dates            预定的日期列表
    //$orderId          关联的订单id
    public static function orderCalendar($cusId,$busId,$dates,$orderId)
    {
        if (empty($cusId) || empty($busId) || empty($dates)) return false;

        //更新导游的日程表
        $count = self::where('user_id',$busId)
                    ->whereIn('date',$dates)
                    ->update(['pre_status'=>DB::Raw('status'),
                                'status'=>'date',
                                'order_id'=>$orderId
                            ]);
//        if ($count != count($dates)) return false;

        //更新游客日程表
        $count = self::where('user_id',$cusId)
                    ->whereIn('date',$dates)
                    ->update(['pre_status'=>DB::Raw('status'),
                                'status'=>'date',
                                'order_id'=>$orderId
                            ]);
                    
        return $count != count($dates);        
    }

	public static function lockGuestUser($userId,$dates,$expireTime,$orderId)
	{
        if(empty($userId) || empty($dates) || empty($orderId)) return false;    
		$count = self::where('user_id',$userId)
			->whereIn('date',$dates)
			->whereIn('status',['free','rest'])
			->whereRaw('(lock_time < '.time()." or order_id=$orderId)")
			->update(['lock_time'=>$expireTime,'order_id'=>$orderId]);


		return $count == count($dates);
	}

	public static function lockPartnerUser($userId,$dates,$expireTime,$orderId)
	{
		if(empty($userId) || empty($dates) || empty($orderId)) return false;	
		$count = self::where('user_id',$userId)
			->whereIn('date',$dates)
			->where('status','free')
			->whereRaw('(lock_time < '.time()." or order_id=$orderId)")
			->update(['lock_time'=>$expireTime,'order_id'=>$orderId]);

		return $count == count($dates);
	}



}

