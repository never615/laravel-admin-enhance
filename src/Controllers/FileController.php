<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\Controllers\Base\QiniuToken;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 13/03/2017
 * Time: 4:33 PM
 */
class FileController extends Controller
{

    use QiniuToken;


    /**
     * 获取七牛上传图片的token
     *
     * @return mixed
     */
    public function getUploadToken()
    {
        $path = \Request::input("path", "file");
        $base64 = \Request::input("base64", 0);

        $token = $this->getUploadTokenInter($path, $base64);

        return response()->json([
            'uptoken' => $token,
        ]);
    }


    /**
     * 处理文件上传 给wangEditor使用
     *
     * @param Request $request
     *
     * @return string
     */
    public function upload(Request $request)
    {

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            $file = $request->file('file');

            //扩展名
            $extension = $file->extension();

            //允许的文件后缀
            $fileTypes = [ 'jpeg', 'png' ];

            //检查类型是否支持
            if ( ! in_array($extension, $fileTypes)) {
                return response("error|" . trans("errors.upload_image_not_support"));
            }

            //检查文件大小是否超过php.ini的设置
            if ($file->getMaxFilesize() < $file->getClientSize()) {
                return response("error|" . trans("errors.upload_size_too_large"));
            }

            $result = Storage::disk("admin")->putFile("editor", $file);

            if ($result == false) {
                return response("error|" . trans("errors.upload_error"));
            }

            //直接返回对应文件的路径
            return response(Storage::disk("admin")->url($result));
        } else {
            return response("error|" . trans("errors.upload_error") . '请求错误');
        }
    }

}
