<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/7
 * Time: 4:46 PM
 */

namespace App\Http\Controllers;


use App\Services\OrderTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderTemplateController
{

    public function lists(Request $request, OrderTemplateService $service)
    {

        $page = $request->input('page', 1);
        $number = $request->input('number', 20);
        $res = $service->lists($page, $number);
        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"],
            "data" => $res['data']
        ]);
    }

    //模板的创建
    public function createTemp(Request $request, OrderTemplateService $service)
    {
        $tempName = $request->input("title");
        $icon = $request->input("icon", "");
        $param = $request->input("params");
        $validator = Validator::make($request->all(), [
            "title" => "required",
            "params" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }
        $res = $service->createTemplate($tempName, $param, $icon);
        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"]
        ]);
    }

    //模板的编辑
    public function editTemp(Request $request, OrderTemplateService $service)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            "temp_id" => "required",
            "params" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $res = $service->editTemp($data["temp_id"], $data);

        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"]
        ]);
    }


    /**
     * 模板的删除
     *
     * @param Request $request
     * @param OrderTemplateService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTemp(Request $request, OrderTemplateService $service)
    {
        $tempId = $request->input("temp_id");
        $validator = Validator::make($request->all(), [
            "temp_id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $res = $service->deleteTemplate($tempId);

        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"]
        ]);
    }

    /**
     * 模板参数的编辑
     *
     * @param Request $request
     * @param OrderTemplateService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function editTempParam(Request $request, OrderTemplateService $service)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            "param_id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }
        $res = $service->editParams($data['param_id'], $data);

        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"]
        ]);
    }

    /**
     * 模板参数的追加
     *
     * @param Request $request
     * @param OrderTemplateService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function appendParam(Request $request, OrderTemplateService $service)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            "temp_id" => "required",
            "name" => "required",
            "length" => "required",
            "type" => "required",
            "show_type" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }
        $res = $service->createParams($data['temp_id'], $data["name"], $data["length"], $data["show_type"], $data["type"]);

        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"]
        ]);
    }

    /**
     * 删除参数
     *
     * @param Request $request
     * @param OrderTemplateService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteParam(Request $request, OrderTemplateService $service)
    {
        $paramId = $request->input("param_id");
        $validator = Validator::make($request->all(), [
            "param_id" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $res = $service->deleteParam($paramId);
        return response()->json([
            "code" => $res["code"],
            "message" => $res["message"]
        ]);
    }

    /**
     * @param Request $request
     * @param OrderTemplateService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dispatchTemp(Request $request, OrderTemplateService $service)
    {
        $tempId = $request->input("temp_id");
        $userId = $request->input("user_id");
        $validator = Validator::make($request->all(), [
            "temp_id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }

        $service->authUser($tempId, $userId);
    }

    /**
     * 设置icon
     *
     * @param Request $request
     * @param OrderTemplateService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function setIcon(Request $request, OrderTemplateService $service)
    {
        $tempId = $request->input("temp_id");
        $userId = $request->input("icon");
        $validator = Validator::make($request->all(), [
            "temp_id" => "required",
            "icon" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 2001,
                "message" => $validator->errors()->first()
            ]);
        }
        $service->setIcon($tempId, $userId);
        return response()->json([
            "code" => 0,
            "message" => "success"
        ]);
    }
}