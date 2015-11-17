<?php

namespace App\Services;

use App\Models\Calendar;

class CalendarService
{

	//fromData默认为当天所在月的开始
	//toDate默认为开始日期的所在月的结束
	public static function getUserCalendarMonth($userId,$month=NULL)
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
			$baseSalary[$value['date']] = $value;
		}

		return $baseSalary;
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
				$rtndata[$day] = ['date'=>$day,'status'=>'rest','week'=>date("w",$preMonthTime)];
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
			$rtndata[$day] = ['date'=>$day,'status'=>'rest','week'=>$curWeek];
			$beginTime += 24*3600;
		} while (true);

		//获取后一个月至周日
		ksort($rtndata);
		return $rtndata;
	}

	public static function insertCal($userId,$date,$status='free')
	{
		if (empty($userId) || empty($date)) return false;

		$calInfo = [
			'user_id' 	=> 	$userId,
			'date' 		=> 	$date,
			'status' 	=> 	empty($status) ? 'free' : $status,
			'month' 	=> 	date('Y-m',strtotime($date))
		];

		return Calendar::insertCal($calInfo);
	}

	//用户下订单预约某人的日期
	// $cusId 			下单人的id
	// $busId 			被预约的人的Id
	// $dates 			预约的日期 数组，array('2015-01-02','2015-10-21')
	public static function order($cusId,$busId,$dates)
	{

	}
	
}

