<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
//use App\Models\Test;

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
