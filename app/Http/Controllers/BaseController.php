<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Response;
use Event;
use Log;
use Input;

abstract class BaseController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    protected function returnJsonResult($code, $desc='', $data='')
    {
        return Response::json(array('code' => $code, 'desc' => $desc, 'data' => $data));
    }
}
