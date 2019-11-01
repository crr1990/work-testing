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
        $job = Order::where("job_name", $jobName)->where("user_id", $userId)->first();
        if (!empty($job)) {
            return [
                "code" => 3001,
                "msg" => "工单重复"
            ];
        }

        $result = Order::create([
            "user_id" => $userId,
            "job_name" => $jobName,
            "temp_id" => $tempId,
            "client" => $client,
            "order_detail" => json_encode($params),
        ]);

       $this->afterCreateJob($params);

        return [
            "code" => 0,
            "msg" => "success",
            "data" => $result->id
        ];
    }

    public function copyJob($id) {
        $job = Order::where("id",$id)->first();
        if(empty($job)) {
            return false;
        }

        $job->job_name = $job->job_name."复制";
        Order::create($job);

    }

    public function afterCreateJob($params) {
        // 调用第三方创建工单数据
        // 获取调用地址
        $res = Dics::where("key_name", "job_url")->first();
        $urlArray = json_decode($res['value'], true);
        $options = [];
        // 组装数据
        foreach ($params as $key => $v) {
            $options[] = [$v["name"] => $v["value"]];
        }
        HttpUrl::get($urlArray['create_url'], $options);
    }

    /**
     * @param $filter
     */
    function orderList($filter)
    {
        $order = Order::where("is_enabled", 1);
        if (isset($filter['userId']) && !empty($filter["userId"])) {
            $order->where("user_id", $filter["userId"]);
        }

        if (isset($filter['startTime']) && !empty($filter["startTime"])) {
            $order->where("create_time", ">=", $filter["startTime"]);
        }

        if (isset($filter['endTime']) && !empty($filter["endTime"])) {
            $order->where("create_time", "<=", $filter["endTime"]);
        }

        if (isset($filter['jobId']) && !empty($filter["jobId"])) {
            $order->where("job_id", $filter["jobId"]);
        }

        if (isset($filter['jobName']) && !empty($filter["jobName"])) {
            $order->where("job_name", $filter["jobName"]);
        }

        if (isset($filter['client']) && !empty($filter["client"])) {
            $order->where("client", $filter["client"]);
        }


        if (!empty($filter["sort"])) {
            switch ($filter["sort"]) {
                case 1:
                    $order->orderBy("create_time", "desc");
                    break;
                case 2:
                    $order->orderBy("job_name", "desc");
                    break;
                case 3:
                    $order->orderBy("client", "desc");
                    break;
                default :
                    $order->orderBy("create_time", "desc");
            }
        } else {
            $order->orderBy("create_time", "desc");
        }
        $total = $order->count();
        $limit = 20;
        $pageTotal = ceil($total / $limit);

        $res = $order->limit($limit)->offset(($filter['page'] - 1) * $limit)->get()->toArray();

        $result = [];
        foreach ($res as $v) {
            $result[] = [
                'id' => $v['id'],
                'jobName' => $v['job_name'],
                'client' => $v['client'],
                'createTime' => $v['create_time'],
                'detail' => json_decode($v['order_detail'])
            ];
        }

        return [
            "total" => $total,
            "pageTotal" => $pageTotal,
            "pageSize" => $limit,
            "currentPage" => $filter['page'],
            "list" => $result,
        ];
    }

    function deleteJob($ids)
    {
        $order = Order::whereIn("id", $ids)->first();
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

        if (isset($data["jobName"]) && !empty($data["jobName"])) {
            $job = Order::where("job_name", $data["jobName"])->first();
            if (!empty($job)) {
                return [
                    "code" => 3001,
                    "msg" => "工单重复"
                ];
            }
            $order->job_name = $data["jobName"];
            $order->save();
        }

        if (isset($data["orderDetail"]) && !empty($data["orderDetail"])) {
            $order->order_detail = json_encode($data["orderDetail"]);
            $order->save();
        }

        return [
            "code" => 0,
            "msg" => "success"
        ];
    }
}