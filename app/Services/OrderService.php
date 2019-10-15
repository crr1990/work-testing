<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/22
 * Time: 7:10 PM
 */

namespace App\Services;

use App\Common\Utils\HttpUrl;
use App\Models\Dics;
use App\Models\Order;
use App\Models\OrderTemplateParams;

class OrderService
{

    /**
     * @param $userId
     * @param $tempId
     * @param $client
     * @param $jobName
     * @param $params array 按顺序传值
     *
     * @return array
     */
    function createOrder($userId, $client, $tempId, $params, $jobName)
    {
        $job = Order::where("job_name", $jobName)->first();
        if (!empty($job)) {
            return [
                "code" => 3001,
                "msg" => "工单重复"
            ];
        }

        $tempParams = OrderTemplateParams::where("temp_id", $tempId)->orderBy("sort")->get()->toArray();

        $options = [];
        // 组装数据
        foreach ($tempParams as $key => $v) {
            $tempParams[$key]["value"] = $params[$key];
            $options[] = [$v["name"] => $params[$key]];
        }

        Order::create([
            "user_id" => $userId,
            "job_name" => $jobName,
            "temp_id" => $tempId,
            "client" => $client,
            "order_detail" => json_encode($tempParams),
        ]);

        // 调用第三方创建工单数据
        // 获取调用地址
        $res = Dics::where("key_name", "job_url")->first();
        $urlArray = json_decode($res, true);


        HttpUrl::get($urlArray['create_url'], $options);

        return [
            "code" => 0,
            "msg" => "success"
        ];
    }

    /**
     * @param $filter
     */
    function orderList($filter)
    {
        $order = Order::where("is_enabled", 1);
        if (isset($filter['user_id']) && !empty($filter["user_id"])) {
            $order->where("user_id", $filter["user_id"]);
        }

        if (isset($filter['start_time']) && !empty($filter["start_time"])) {
            $order->where("create_time", ">=", $filter["start_time"]);
        }

        if (isset($filter['end_time']) && !empty($filter["end_time"])) {
            $order->where("create_time", "<=", $filter["end_time"]);
        }

        if (isset($filter['job_id']) && !empty($filter["job_id"])) {
            $order->where("job_id", $filter["job_id"]);
        }

        if (isset($filter['job_name']) && !empty($filter["job_name"])) {
            $order->where("job_name", $filter["job_name"]);
        }

        return $order->limit(20)->offset(($filter['page'] - 1) * 20)->get()->toArray();
    }

    function deleteJob($id)
    {
        $order = Order::where("id", $id)->first();
        $order->is_enabled = 0;
        $order->save();
    }

    function editJob($id, $data)
    {
        $order = Order::where("id", $id)->first();
        if (empty($order)) {
            return [
                "code" => 3002,
                "msg" => "工单不存在"
            ];
        }

        if (isset($data["job_name"]) && !empty($data["job_name"])) {
            $job = Order::where("job_name", $data["job_name"])->first();
            if (!empty($job)) {
                return [
                    "code" => 3001,
                    "msg" => "工单重复"
                ];
            }
            $order->job_name = $data["job_name"];
            $order->save();
        }

        if (isset($data["order_detail"]) && !empty($data["order_detail"])) {
            $order->order_detail = json_encode($data["order_detail"]);
            $order->save();
        }

        return [
            "code" => 0,
            "msg" => "success"
        ];
    }
}