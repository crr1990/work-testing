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
        header("Content-Type:text/event-stream");
        while (true) {
            echo "data:".date("Y-m-d H:i:s")."\n\n";
            @ob_flush();@flush();
            sleep(1);
        }
    }
}
