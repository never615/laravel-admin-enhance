<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Traits;

use Mallto\Admin\Controllers\Base\QiniuToken;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2019/1/25
 * Time: 4:19 PM
 */
trait  AdminFileHelp
{
    use QiniuToken;

    protected function formMultipleImage(
        $form,
        $columnName,
        $tableName = "easy",
        $displayName = null,
        $help = "建议尺寸750x500"
    ) {
        $form->multipleImage($columnName, $displayName)
            ->help("图片最大不能超过2M<br>".$help)
            ->options([
                'maxFileSize'     => '2048',
                "msgSizeTooLarge" => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
            ])
            ->removable()
            ->uniqueName()
            ->move("$tableName/$columnName/".$this->currentId);
    }


    protected function formImage($form, $columnName, $tableName = "easy", $displayName = null, $help = "")
    {
        $form->image($columnName, $displayName)
            ->help("图片最大不能超过2M<br>".$help)
            ->options([
                'maxFileSize'     => '2048',
                "msgSizeTooLarge" => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
            ])
            ->uniqueName()
            ->removable()
            ->move("$tableName/$columnName/".$this->currentId);
    }


    protected function formVideo($form, $columnName, $tableName = "easy", $displayName = "视频")
    {
        $form->qiniuFile($columnName, $displayName)
            ->options([
                'initialPreviewConfig'    => [
                    ['key' => 0, 'filetype' => 'video/mp4'],
                ],
                'initialPreviewFileType'  => 'video',
                'allowedFileTypes'        => ['video'],
//                'dropZoneEnabled'         => false,
                'uploadLabel'             => '上传',
                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'               => 'https://up-z2.qbox.me/',
                'uploadExtraData'         => [
                    'token' => $this->getUploadTokenInter("$tableName/$columnName/".$this->currentId),
                ],
                'allowedFileExtensions'   => ['mp4'],
                'maxFileCount'            => 1, //同时上传的文件数量
            ])
            ->help("视频只支持mp4格式文件,添加视频后需点击上传按钮上传,只能上传一个");
    }


    protected function formAudio($form, $columnName, $tableName = "easy", $displayName = "语音")
    {
        $form->qiniuFile($columnName, $displayName)
            ->options([
                'initialPreviewConfig'    => [
                    ['key' => 0, 'filetype' => 'audio/mp3'],
                ],
                'initialPreviewFileType'  => 'audio',
                'allowedFileTypes'        => ['audio'],
                'uploadLabel'             => '上传',
                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'               => 'https://up-z2.qbox.me/',
                'uploadExtraData'         => [
                    'token' => $this->getUploadTokenInter("$tableName/$columnName/".$this->currentId),
                ],
                'maxFileCount'            => 1, //同时上传的文件数量
            ])
            ->help("添加文件后请点击上传按钮");
    }

}