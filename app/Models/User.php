<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Auth;
use Session;
use Config;
use Hash;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'user';


    protected $primaryKey = 'user_id';

    protected $dateFormat = 'U';

    protected $fillable = [
                            'user_id',
                            'nickname',
                            'mobile',
                            'email',
                            'password',
                            'head_image',
                            'created_at',
                            'updated_at',
                            'remember_token'
                            ];    
    

    public static function getUser($userId)
    {
        $info = self::where('user_id',$userId)->first();

        return empty($info) ? $info : $info->toArray();   
    }


    public static function insertUser($userInfo)
    {
        if (empty($userInfo)) return false;
        $userInfo['password'] = Hash::make($userInfo['password']);
        $result = self::create($userInfo);
        return empty($result) ?$result : $result->user_id;
    }

    public static function resetUserPasswordByAccount($account,$password)
    {
        if (empty($account) || empty($password)) return false;

        if (is_numeric($account)) 
            return 1 == self::where('mobile',$account)->update(['password'=>Hash::make($password)]);
        else
            return 1 == self::where('email',$account)->update(['password'=>Hash::make($password)]);            
    }

    /*
    |--------------------------------------------------------------------------
    | 查询账号是否存在 
    |--------------------------------------------------------------------------
    | 
    | 如果存在 返回true
    | 如果不能存在 返回false
    |
    */    
    public static function isAccountExist($account)
    {
        $id = 0;
        if (is_numeric($account)) $id = self::where('mobile',$account)->value('user_id');
        else $id = self::where('email',$account)->value('user_id');

        return $id > 0;
    }

    public function attempt($val1,$val2)
    {
        var_dump($val1);
        var_dump($val2);
    }

}

