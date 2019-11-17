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
use App\Models\User;
use Mockery\Exception;
use Illuminate\Support\Facades\DB;

class OrderTemplateService
{
    function lists($page, $number, $userId)
    {
        $temps = $this->tempIdByUser($userId);
        $total = OrderTemplate::where("is_enabled", 1)->count();
        $pages = ceil($total / $number);
        $offset = ($page - 1) * $number;

        $query = OrderTemplate::with(['params' => function ($q) {
            $q->where("is_enabled", 1)
                ->select('temp_id', 'name', 'name_length', 'content_length', 'row', 'col', 'show_type', 'type', 'option')
                ->orderBy('row', 'asc')->orderBy('col', 'asc');
        }]);


        if ($userId > 0) {
            $user = User::where("id",$userId)->first();

            if(!empty($user) && $user->type == 0) {
                $query = $query->whereIn("id", $temps);
            }
        }

        $list = $query->where("is_enabled", 1)
            ->limit($number)
            ->offset($offset)
            ->get()
            ->toArray();

        $result = [];
        foreach ($list as $v) {
            $param = [];
            foreach ($v['params'] as $p) {
                $param[] = [
                    "name" => $p['name'],
                    "name_length" => $p['name_length'],
                    "content_length" => $p['content_length'],
                    "row" => $p['row'],
                    "col" => $p['col'],
                    "show_type" => $p['show_type'],
                    "type" => $p['type'],
                    "option" => $p['option'] ? json_decode($p['option'], true) : "",
                ];
            }


            $result[] = [
                'temp_id' => $v['id'],
                'title' => $v['title'],
                'icon' => $v['icon'],
                'params' => $param

            ];
        }
        $data = [
            'list' => $result,
            'pageSize' => intval($number),
            'total' => intval($total),
            'pageTotal' => intval($pages),
            'pageCurrent' => intval($page)
        ];

        return ['code' => 0, 'message' => 'success', 'data' => $data];

    }

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
        DB::beginTransaction();
        $res = OrderTemplate::create($data);

        if (!is_array($params)) {
            DB::rollBack();
            return ["code" => 2010, "message" => "参数格式错误"];
        }

        $insertParams = [];

        foreach ($params as $k => $v) {
            $insertParams[$k] = [
                "name" => $v["name"],
                "temp_id" => $res->id,
                "name_length" => $v["nameLength"],
                "content_length" => $v["contentLength"],
                "col" => $v["col"],
                "row" => $v["row"],
                "sort" => $k,
                "option" => json_encode($v['option']),
                "show_type" => $v["showType"],
                "type" => $v["type"]
            ];
        }

        OrderTemplateParams::insert($insertParams);
        DB::commit();
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


        DB::beginTransaction();
        $temp->save();
        OrderTemplateParams::where('temp_id', $tempId)->delete();
        $params = $data["params"];

        if (!is_array($params)) {
            DB::rollBack();
            return ["code" => 2010, "message" => "参数格式错误"];
        }

        $insertParams = [];
        foreach ($params as $k => $v) {
            $insertParams[$k] = [
                "name" => $v["name"],
                "temp_id" => $tempId,
                "name_length" => $v["nameLength"],
                "content_length" => $v["contentLength"],
                "col" => $v["col"],
                "row" => $v["row"],
                "sort" => $k,
                "option" => json_encode($v['option']),
                "show_type" => $v["showType"],
                "type" => $v["type"]
            ];
        }

        OrderTemplateParams::insert($insertParams);
        DB::commit();
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

    public function setIcon($tempId, $icon)
    {
        $temp = OrderTemplate::where("id", $tempId)->first();
        if (!$temp) {
            return ["code" => 2022, "message" => "模板不存在!"];
        }

        $temp->icon = $icon;
        $temp->save();
    }

    public function tempIdByUser($userId)
    {
        return OrderTemplateUser::where("user_id", $userId)->pluck("temp_id")->toArray();
    }
}