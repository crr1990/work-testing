<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/11/2
 * Time: 11:15 PM
 */


while (true) {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    header('Access-Control-Allow-Origin:*');
    header("Connection", "keep-alive");

    // Set data line
    print "Event: server-time" . PHP_EOL;
    print "data: " . date( 'G:H:s', time() ) . PHP_EOL;
    print PHP_EOL;

    ob_end_flush();     // Strange behaviour, will not work
    flush();            // Unless both are called !

    // Wait one second.
    sleep(5);
}