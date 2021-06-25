<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Traits;

use Encore\Admin\Form;
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

    /**
     * 直传对象存储的多图控件
     *
     * @param        $form
     * @param        $columnName
     * @param string $tableName
     * @param null   $displayName
     * @param string $help
     */
    protected function formMultipleImage2(
        $form,
        $columnName,
        $tableName = "easy",
        $displayName = null,
        $help = "建议尺寸750x500"
    ) {

        $form->qiniuMultipleFile($columnName, $displayName)
            ->help("单张图片最大不能超过2M<br>添加图片后需要点击上传按钮" . $help)
            ->options([
                'maxFileSize'             => '2048',
                "msgSizeTooLarge"         => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
                'allowedFileTypes'        => [ 'image' ],
//                'dropZoneEnabled' temp.val(files);        => false,
                'uploadLabel'             => '上传',
                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'               => 'https://up-z2.qbox.me/',
                'uploadExtraData'         => [
                    'token' => $this->getUploadTokenInter("$tableName/$columnName/" . $this->currentId),
                ],
                'allowedFileExtensions'   => [ 'mp4' ],
            ])
            ->sortable()
            ->removable()
            ->uniqueName()
            ->move("$tableName/$columnName/" . $this->currentId);
    }


    /**
     * 多图控件
     *
     * @param        $form
     * @param string $columnName
     * @param string $tableName
     * @param null   $displayName
     * @param string $help
     */
    protected function formMultipleImage(
        $form,
        $columnName = "images",
        $tableName = "easy",
        $displayName = null,
        $help = "建议尺寸750x500"
    ) {

        $form->multipleImage($columnName, $displayName)
            ->help("上传完成,点击提交数据后,可以拖动图片修改顺序<br>图片最大不能超过2M<br>" . $help)
            ->options([
                'maxFileSize'     => '2048',
                "msgSizeTooLarge" => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
            ])
            ->sortable()
            ->removable()
            ->uniqueName()
            ->validator(function ($callback) {
                return false;
            })
            ->move("$tableName/$columnName/" . $this->currentId);
    }


    /**
     * 单图
     *
     * @param        $form
     * @param        $columnName
     * @param string $tableName
     * @param null   $displayName
     * @param string $help
     */
    protected function formImage(
        $form,
        $columnName,
        $tableName = "easy",
        $displayName = null,
        $help = "建议尺寸500x500"
    ) {
        $form->image($columnName, $displayName)
            ->help("图片最大不能超过2M." . $help)
            ->options([
                'maxFileSize'     => '2048',
                "msgSizeTooLarge" => '文件 "{name}" ({size} KB) 超过了允许上传的最大限制: {maxSize} KB!',
            ])
            ->uniqueName()
            ->removable()
            ->move("$tableName/$columnName/" . $this->currentId);
    }


    /**
     * 视频
     *
     * @param        $form
     * @param        $columnName
     * @param string $tableName
     * @param string $displayName
     */
    protected function formVideo($form, $columnName, $tableName = "easy", $displayName = "视频")
    {
        $form->qiniuFile($columnName, $displayName)
            ->options([
                'initialPreviewFileType'  => 'video',
                // video is the default and can be overridden in config below
                'initialPreviewConfig'    => [
                    [ 'key' => 0, 'filetype' => 'video/mp4' ],
                ],
                'allowedFileTypes'        => [ 'video' ],
//                'dropZoneEnabled'         => false,
                'uploadLabel'             => '上传',
                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'               => 'https://up-z2.qbox.me/',
                'uploadExtraData'         => [
                    'token' => $this->getUploadTokenInter("$tableName/$columnName/" . $this->currentId),
                ],
                'allowedFileExtensions'   => [ 'mp4' ],
                'maxFileCount'            => 1,
                //同时上传的文件数量
            ])
            ->help("视频只支持mp4格式文件,添加视频后需点击上传按钮上传,只能上传一个");
    }


    /**
     * 音频
     *
     * @param        $form
     * @param        $columnName
     * @param string $tableName
     * @param string $displayName
     */
    protected function formAudio($form, $columnName, $tableName = "easy", $displayName = "语音")
    {
        $form->qiniuFile($columnName, $displayName)
            ->options([
                'initialPreviewConfig'    => [
                    [ 'key' => 0, 'filetype' => 'audio/mp3' ],
                ],
                'initialPreviewFileType'  => 'audio',
                'allowedFileTypes'        => [ 'audio' ],
                'uploadLabel'             => '上传',
                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'               => 'https://up-z2.qbox.me/',
                'uploadExtraData'         => [
                    'token' => $this->getUploadTokenInter("$tableName/$columnName/" . $this->currentId),
                ],
                'maxFileCount'            => 1, //同时上传的文件数量
            ])
            ->help("添加文件后请点击上传按钮");
    }

}
