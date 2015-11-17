<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Log;

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
                            'callendar_id',
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

    public static function insertCal($calInfo)
    {
        if (empty($calInfo)) return false;
        $result = self::create($calInfo);
        return empty($result) ?$result : $result->callendar_id;
    }

    public static function getCalByDates($userId,$dates)
    {
        if (empty($user) || empty($dates)) return false;
        if (!is_array($dates)) $dates = array($dates);

        $result = self::where('user_id',$userId)
                  ->whereIn('date',$dates)
                  ->get();

        return empty($result) ? false : $result->toArray();                    
    }



}

