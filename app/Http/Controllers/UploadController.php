<?php
/**
 * Created by PhpStorm.
 * User: chenrongrong
 * Date: 2019/9/7
 * Time: 4:54 PM
 */

namespace App\Http\Controllers;


use App\Services\FileService;
use App\Services\Upload;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function upload(Request $request, FileService $service)
    {
        $dir = $request->input("dir_path", "");
        $userId = $request->input("user_id", "");
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $file->move($dir, $fileName);
        $service->afterUpload($fileSize, $userId);
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