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
    

    public static function createOrder($info)
    {
        
    }

}

