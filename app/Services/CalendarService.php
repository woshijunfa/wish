<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\Utility;
use DB;

class CalendarService
{

	//fromData默认为当天所在月的开始
	//toDate默认为开始日期的所在月的结束
	//$weekFormat是否按照周格式给出
	//正确返回数据，错误返回false
	public static function getUserCalendarMonth($userId,$month=NULL,$weekFormat=true)
	{
		//user_id check
		if (empty($userId)) return false;

		//from date check
		if (empty($month)) $fromDate = date('Y-m',time());

		//获取用户的信息
		$cals = Calendar::getByMonth($userId,$month);
		if (false === $cals) return false;

		//生成base salary
		$baseSalary = self::getBaseCalendarOfMonth($month);

		//生成本月基准安排
		foreach ($cals as $value) 
		{
			$value['week'] = $baseSalary[$value['date']]['week'];
			$value['day'] = $baseSalary[$value['date']]['day'];
			$baseSalary[$value['date']] = $value;
		}

		$rtndata = $baseSalary;
		if ($weekFormat) 
		{
			$rtndata = [];
			$weekarr = [];
			foreach ($baseSalary as $value) 
			{
				$weekarr[] = $value;
				if (count($weekarr) >= 7) 
				{
					$rtndata[] = $weekarr;
					$weekarr = [];
				}
			}
		}

		return $rtndata;
	}


	//获取这个月的日历
	public static function getBaseCalendarOfMonth($month=NULL)
	{
		if (empty($month)) $month = date('Y-m',time());

		$rtndata = array();

		$beginTime = strtotime($month);
		$day = NULL;

		$curWeek = date('w',$beginTime);
		$preMonthTime = $beginTime - 24*3600;
		if ($curWeek == 0) $curWeek = 7;
		if ($curWeek != 1) 
		{
			while (true) 
			{
				$curMonth = date('Y-m',$preMonthTime);
				$curWeek = date('w',$preMonthTime);
				$day = date('Y-m-d',$preMonthTime);
				$rtndata[$day] = ['date'=>$day,'status'=>'rest','month'=>$curMonth,'week'=>date("w",$preMonthTime),'day'=>(int)date("d",$preMonthTime)];
				if ($curWeek == 1) break;
				$preMonthTime -= 24*3600;
			}
		}

		//获取前一个月至周1
		do 
		{
			$curMonth = date('Y-m',$beginTime);
			$curWeek = date('w',$beginTime);
			$day = date('Y-m-d',$beginTime);
			if ($month != $curMonth && $curWeek ==1) break;
			$rtndata[$day] = ['date'=>$day,'status'=>'rest','month'=>$curMonth,'week'=>$curWeek,'day'=>(int)date("d",$beginTime)];
			$beginTime += 24*3600;
		} while (true);

		//获取后一个月至周日
		ksort($rtndata);
		return $rtndata;
	}

	public static function insertCals($userId,$dates,$status='free')
	{
		if (empty($userId) || empty($dates)) return false;
		if (!is_array($dates)) $dates = [$dates];

		$cals = [];
		$curtime = time();
		foreach ($dates as $date) {
			$cals[] = [
				'user_id' 		=> 	$userId,
				'date' 			=> 	$date,
				'status' 		=> 	empty($status) ? 'free' : $status,
				'month' 		=> 	date('Y-m',strtotime($date)),
				'created_at' 	=> 	$curtime,
				'updated_at' 	=> 	$curtime
			];
		}

		return Calendar::insertCals($cals);
	}	

	//检查Guest用户这些天是否有安排，如果有则返回false，如果没有创建默认行程
	//$userId 			用户id
	//$dates 			提起列表
	//成功返回 true
	//
	public static function checkUserCalendar($userId,$dates)
	{
		if (empty($userId) || empty($dates)) return false;

		//选出这些日期信息查看是否有安排，如果没有则创建
		$cals = Calendar::getCalByDates($userId,$dates);
		if (false === $cals) return false;

		$hasVals = [];
		//检查是否已经有预约的日期
		foreach ($cals as $cal) 
		{
			if ($cal['status'] == 'date') return false;
			$hasVals[] = $cal['date'];
		}

		$createVal = array_diff($dates,$hasVals);
		if (empty($createVal)) return true;
		
		return self::insertCals($userId,$createVal);
	}

	public static function LockUser($userId,$partnerId,$dates,$expireTime,$orderId)
	{
		
		try
		{
        		DB::beginTransaction();
			$isok = Calendar::LockGuestUser($userId,$dates,$expireTime,$orderId);
			if(!$isok)
			{
				DB::rollBack();
				return "请检查日期有安排或者有未支付订单";
			}

			$isok = Calendar::LockPartnerUser($partnerId,$dates,$expireTime,$orderId);
			if(!$isok)
			{
				DB::rollBack();
				return "用户选定日期有安排，或被锁定，请刷新再试";
			}

			DB::commit();
			return true;
		}
		catch(\Exception $e)
		{
			DB::rollBack();
			Utility::LogException($e);
			return false;
		}

	}
}

