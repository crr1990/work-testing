<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/21
 * Time: 2:49 PM
 */

namespace App\Services;

use App\Models\OrderTemplate;
use App\Models\OrderTemplateParams;
use App\Models\OrderTemplateUser;
use Mockery\Exception;

class OrderTemplateService
{
    /**
     * 创建模板
     *
     * @param string $title 模板标题
     * @param string $params 参数json串
     *
     * @return array
     */
    public function createTemplate($title, $params, $icon)
    {

        $temp = OrderTemplate::where("title", $title)->where("is_enabled", 1)->first();
        if ($temp) {
            return ["code" => 2011, "message" => "模板名称已存在"];
        }
        $data = [
            "title" => $title,
            "icon" => $icon
        ];
        $res = OrderTemplate::create($data);

        // 解析params
        try {
            $param = \GuzzleHttp\json_decode($params, true);
        } catch (Exception $exception) {
            return ["code" => 2010, "message" => $exception->getMessage()];
        }

        if (!is_array($param)) {
            return ["code" => 2010, "message" => "参数格式错误"];
        }

        $insertParams = [];
        foreach ($param as $k => $v) {
            $insertParams[$k] = [
                "name" => $v["name"],
                "temp_id" => $res->id,
                "length" => $v["length"],
                "sort" => $k,
                "show_type" => $v["show_type"],
                "type" => $v["type"]
            ];
        }

        OrderTemplateParams::insert($insertParams);
        return ["code" => 0, "message" => "success"];
    }

    /**
     * 删除模板
     *
     * @param $tempId
     * @return mixed
     */
    public function deleteTemplate($tempId)
    {
        $temp = OrderTemplate::where("id", $tempId)->first();
        if (!$temp) {
            return ["code" => 2022, "message" => "模板不存在!"];
        }
        $temp->is_enabled = 0;
        $temp->save();
        return ["code" => 0, "message" => "success"];
    }

    /**
     * 分配用户模板使用权限
     *
     * @param $tempId
     * @param $userId
     *
     * @return array
     */
    public function authUser($tempId, $userId)
    {
        $res = OrderTemplateUser::where("user_id", $userId)->where("temp_id", $tempId)->first();
        if ($res) {
            return ["code" => 2023, "message" => "已经分配过了!"];
        }

        OrderTemplateUser::create([
            "user_id" => $userId,
            "temp_id" => $tempId
        ]);

        return ["code" => 0, "message" => "success"];
    }

    /**
     * 编辑模板
     *
     * @param $tempId
     * @param $data
     *
     * @return array
     */
    public function editTemp($tempId, $data)
    {
        $temp = OrderTemplate::where("id", $tempId)->first();
        if (!$temp) {
            return ["code" => 2022, "message" => "模板不存在!"];
        }

        $temp->title = isset($data["title"]) && !empty($data["title"]) ? $data["title"] : $temp->title;
        $temp->icon = isset($data["icon"]) && !empty($data["icon"]) ? $data["icon"] : $temp->icon;
        $temp->save();

        return ["code" => 0, "message" => "success"];
    }

    /**
     * 编辑参数详情
     *
     * @param $paramsId
     * @param $data
     * @return array
     */
    public function editParams($paramsId, $data)
    {
        $param = OrderTemplateParams::where("id", $paramsId)->first();
        if (!$param) {
            echo "参数不存在！" . PHP_EOL;
            return ["code" => 2020, "message" => "参数不存在!"];
        }

        $param->name = isset($data["name"]) ? $data["name"] : $param->name;
        $param->length = isset($data["length"]) ? $data["length"] : $param->length;
        $param->type = isset($data["type"]) ? $data["type"] : $param->type;
        $param->show_type = isset($data["show_type"]) ? $data["show_type"] : $param->show_type;
        $param->save();

        return ["code" => 0, "message" => "success"];
    }


    /**
     * 创建单个参数
     *
     * @param $tempId
     * @param $name
     * @param $length
     * @param $showType
     * @param $type
     * @return array
     */
    public function createParams($tempId, $name, $length, $showType, $type)
    {
        OrderTemplateParams::create([
            "temp_id" => $tempId,
            "name" => $name,
            "length" => $length,
            "show_type" => $showType,
            "type" => $type,
        ]);

        return ["code" => 0, "message" => "success"];
    }

    /**
     * 删除参数
     *
     * @param $paramId
     * @return array
     */
    public function deleteParam($paramId)
    {
        $param = OrderTemplateParams::where("id", $paramId)->first();

        if (!$param) {
            echo "参数不存在！" . PHP_EOL;
            return ["code" => 2020, "message" => "参数不存在!"];
        }

        $param->is_enabled = 0;
        $param->save();
        return ["code" => 0, "message" => "success"];
    }
}