<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/22
 * Time: 7:10 PM
 */

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTemplateParams;

class OrderService
{

    /**
     * @param $userId
     * @param $tempId
     * @param $params array 按顺序传值
     */
    function createOrder($userId, $client, $tempId, $params)
    {
        $tempParams = OrderTemplateParams::where("temp_id", $tempId)->orderBy("sort")->get()->toArraty();

        // 组装数据
        foreach ($tempParams as $key => $v) {
            $v["value"] = $params[$key];
        }

        return Order::create([
            "user_id" => $userId,
            "temp_id" => $tempId,
            "client" => $client,
            "order_detail" => \GuzzleHttp\json_encode($tempParams),
            "create_time" => time()
        ]);
    }
}