<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Auth;
//use App\Models\Test;
use App\Models\Calendar;
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

        var_dump(Calendar::getCalByDates(2,'2015-10-13'));
die;
        var_dump(CalendarService::getUserCalendarMonth(1,'2015-10'));
        var_dump(CalendarService::insertCals(2,['2015-10-12','2015-10-13','2015-10-14']));
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

