<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/7
 * Time: 4:46 PM
 */

namespace App\Http\Controllers;


use App\Common\Utils\HttpUrl;
use App\Models\Dics;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController
{
    // 工单执行
    public function start(Request $request)
    {
        // 获取调用地址
        $res = Dics::where("key_name", "job_url")->first();
        $urlArray = json_decode($res);

        HttpUrl::get($urlArray['start_url'], $request->all());
    }

    /**
     * 创建工单
     *
     * @param Request $request
     * @param OrderService $service
     * @return \Illuminate\Http\JsonResponse
     */
    function createJob(Request $request, OrderService $service)
    {
        $validator = Validator::make($request->all(), [
            "temp_id" => "required",
            "user_id" => "required",
            "job_name" => "required",
            "client" => "required",
            "order_detail" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $data = $request->all();
        $res = $service->createOrder($data["user_id"], $data["client"], $data["temp_id"], $data["order_detail"], $data["job_name"]);
        return response()->json([
            "code" => $res["code"],
            "message" => $res["msg"]
        ]);
    }

    /**
     * 删除工单
     *
     * @param Request $request
     * @param OrderService $service
     * @return \Illuminate\Http\JsonResponse
     */
    function deleteJob(Request $request, OrderService $service)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get("id");
        $service->deleteJob($id);
        return response()->json([
            "code" => 0,
            "message" => "success"
        ]);
    }

    /**
     * 编辑工单
     *
     * @param Request $request
     * @param OrderService $service
     * @return \Illuminate\Http\JsonResponse
     */
    function editJob(Request $request, OrderService $service)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $res = $service->editJob($request->get("id"), $request->all());
        return response()->json([
            "code" => $res["code"],
            "message" => $res["msg"]
        ]);

    }

    /**
     * 获取工单列表
     *
     * @param Request $request
     * @param OrderService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJobList(Request $request, OrderService $service)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            "page" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $list = $service->orderList($data);
        return response()->json([
            "code" => 0,
            "message" => "success",
            "data" => ["list" => $list, "currentPage" => $data["page"]]
        ]);
    }


}