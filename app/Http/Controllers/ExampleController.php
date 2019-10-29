<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function test() {
        header('Access-Control-Allow-Origin:*');
        header("Cache-Control", "no-cache");
	    header("Connection", "keep-alive");
        header("Content-Type:text/event-stream");
        while (true) {
            echo "data:".date("Y-m-d H:i:s",time())."\n\n";
            ob_flush();flush();
            sleep(1);
        }
    }
}
