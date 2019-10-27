<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/7
 * Time: 4:54 PM
 */

namespace App\Http\Controllers;


use App\Models\Dics;
use App\Services\FileService;
use App\Services\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function upload(Request $request, FileService $service)
    {
        $dir = $request->get("dir_path", "/data0/icons");
        $file = $request->file('file');
        $validator = Validator::make($request->all(), [
            "file" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "code" => 1004,
                "message" => $validator->errors()->first()
            ]);
        }
        $fileName = $file->getClientOriginalName();
        $newFileName = time()."_".$fileName;
        $file->move($dir, $newFileName);
        $res = Dics::where("key_name", "icon_url")->first();

        $urlArray = json_decode($res->value,true);
        $data = ["url" => $urlArray['host'] . "/".$newFileName];
        return response()->json([
            'code' => 0,
            'msg' => "success",
            'data' => $data,
        ]);
    }

    public function blockUpload(Request $request)
    {
        $file = $request->file('file');
        $blobNum = $request->file('blob_num');
        $totalBlobNum = $request->file('total_blob_num');
        $upload = new Upload($file->getPathname(), $blobNum, $totalBlobNum, $file->getFilename());
        $upload->apiReturn();
    }
}