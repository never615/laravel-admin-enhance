<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Qiniu\Auth;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 08/03/2017
 * Time: 3:05 PM
 */
trait QiniuToken
{

    /**
     * 获取七牛图片上传token
     *
     * @param string $path
     * @param bool   $base64
     *
     * @return string
     */
    public function getUploadTokenInter($path = "file", $base64 = false)
    {

        $bucket = config("filesystems.disks.qiniu.bucket");
        $auth = new Auth(config("filesystems.disks.qiniu.access_key"),
            config("filesystems.disks.qiniu.secret_key"));

        $returnBody = [
            'key' => "$(key)",
        ];

        $path = trim($path, '/');

        if ($base64) {
            $saveKey = $path . '/' . uniqid() . "$(etag)";
        } else {
            $saveKey = $path . '/' . uniqid() . '$(etag)$(ext)';
        }

        $policy = [
            'returnBody' => json_encode($returnBody, true),
            'saveKey'    => $saveKey,
        ];

        // 生成上传Token
        return $auth->uploadToken($bucket, null, 3600, $policy);
    }

}
