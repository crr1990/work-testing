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
use App\Models\User;
use GuzzleHttp\Client;

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

        $year = date('Y', time());
        $month = date('m', time());
        $day = date('d', time());
        $user = User::where('id', $userId)->first();
        $path = $user->name . '/' . $year . '/' . $month . '/' . $day . '/' . $jobName;

        $taskID = $this->afterCreateJob($params, $jobName);
        if (empty($taskID)) {
            return [
                "code" => 3000,
                "msg" => "创建工单失败",
                "data" => []
            ];
        }
        $result = Order::create([
            "user_id" => $userId,
            "job_name" => $jobName,
            "temp_id" => $tempId,
            "client" => $client,
            "order_detail" => json_encode($params),
            'file_path' => $path,
            'task_id' => $taskID,
        ]);


        return [
            "code" => 0,
            "msg" => "success",
            "data" => ['id' => $result->id, 'savePath' => $path, 'taskId' => $taskID]
        ];
    }

    public function copyJob($id)
    {
        $job = Order::where("id", $id)->first();
        if (empty($job)) {
            return [
                'code' => 2001,
                'msg' => '工单不存在',
                'data' => ''
            ];
        }

        $param = [
            "job_name" => $job->job_name . "复制",
            "temp_id" => $job->temp_id,
            "user_id" => $job->user_id,
            "client" => $job->client,
            "order_detail" => $job->order_detail
        ];
        $res = Order::create($param);
        return ['code' => 0, "msg" => "success", "data" => $res->id];
    }

    function xmltoarr($path)
    {//xml字符串转数组
        $xml = $path;//XML文件
        $objectxml = simplexml_load_string($xml);//将文件转换成 对象
        $xmljson = json_encode($objectxml);//将对象转换个JSON
        $xmlarray = json_decode($xmljson, true);//将json转换成数组
        return $xmlarray;
    }


    public function afterCreateJob($params, $jobName)
    {
        // 调用第三方创建工单数据
        // 获取调用地址
        $res = Dics::where("key_name", "job_url")->first();
        $urlArray = json_decode($res->value, true);

        $options = [];
        // 组装数据
        foreach ($params as $key => $v) {
            $options[$v["name"]] = $v["value"];
        }

        $options["jobName"] = $jobName;

        $result = $this->get($urlArray['create_url'], $options);
        if (empty($result)) {
            return false;
        }
        $result = $this->xmltoarr($result);
        return substr(trim($result['response']), 7);
    }

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
            $order->where("job_name", "like", "%" . $filter["jobName"] . "%");
        }

        if (isset($filter['client']) && !empty($filter["client"])) {
            $order->where("client", "like", "%" . $filter["client"] . "%");
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
                'tempId' => $v['temp_id'],
                'client' => $v['client'],
                'createTime' => $v['create_time'],
                'detail' => json_decode($v['order_detail']),
                'savePath' => $v['file_path'],
                'taskId' => $v['task_id']
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
        Order::whereIn("id", $ids)->update(['is_enabled' => 0]);
    }

    function editJob($id, $data)
    {
        $order = Order::where("id", $id)->first();
        if (empty($order)) {
            return [
                "code" => 3002,
                "msg" => "工单不存在",
                "data" => []
            ];
        }

        if (!empty($data["jobName"]) && ($data["jobName"] != $order->job_name)) {
            $job = Order::where("job_name", $data["jobName"])
                ->where("user_id", $data['userId'])
                ->first();
            if (!empty($job)) {
                return [
                    "code" => 3001,
                    "msg" => "工单重复",
                    "data" => []
                ];
            }
            $order->job_name = $data["jobName"];
        }

        if (isset($data["orderDetail"]) && !empty($data["orderDetail"])) {
            $order->order_detail = json_encode($data["orderDetail"]);
        }

        if (isset($data["client"]) && !empty($data["client"])) {
            $order->client = $data["client"];
        }

        $year = date('Y', time());
        $month = date('m', time());
        $day = date('d', time());
        $user = User::where('id', $order['user_id'])->first();
        $order->file_path = $user->name . '/' . $year . '/' . $month . '/' . $day . '/' . $order->job_name;

        $params = json_decode($order->order_detail, true);
        $taskId = $this->afterCreateJob($params, $order->job_name);

        $order->task_id = $taskId;
        if (empty($taskId)) {
            return [
                "code" => 3000,
                "msg" => "工单编辑，调用create服务失败",
                "data" => []
            ];
        }
        $order->save();
        return [
            "code" => 0,
            "msg" => "success",
            "data" => ["savePath" => $order->file_path, "taskId" => $taskId]
        ];
    }

    public function get($url, $query)
    {
        $client = new Client();


        $array = [
            'headers' => [],
            'query' => $query,
            'http_errors' => false   #支持错误输出
        ];
        try {
            $response = $client->request('GET', $url, $array);
            return $response->getBody()->getContents();
        } catch (\Exception $exception) {
            return null;
        }

    }

    public function startJob($params)
    {
        // 调用第三方创建工单数据
        // 获取调用地址
        $res = Dics::where("key_name", "job_url")->first();
        $urlArray = json_decode($res->value, true);

        $options = [];
        // 组装数
        //$options["jobId"] = $jobId;
        $order = Order::where("id", $params['id'])->first();
        if (!$order) {
            return [
                'code' => 4001,
                'message' => '工单不存在'
            ];
        }
        $user = User::where("id", $params['userId'])->first();
        if (!$user) {
            return [
                'code' => 4002,
                'message' => '用户不存在'
            ];
        }
        $options["jobname"] = $user->name . "-" . $order->job_name;
        $result = $this->get($urlArray['start_url'], $options);
        if (empty($result)) {
            return [
                'code' => 4003,
                'message' => '启动失败'
            ];
        }
        $order->start_times = $order->start_times+1;
        $order->save();
        return [
            'code' => 0,
            'message' => 'success',
            'data' => $this->xmltoarr($result),
        ];
    }
}