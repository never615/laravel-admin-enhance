<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Controllers\Base\QiniuToken;
use Mallto\Admin\Data\Video;

/**
 * 视频文件管理
 * Class UploadController
 *
 * @package Encore\Admin\Controllers
 */
class VideoController extends AdminCommonController
{

    use QiniuToken;

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "视频素材管理";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Video::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->name("视频名称");
        $grid->column('url', "视频地址")->urlWrapper();
    }

    protected function formOption(Form $form)
    {
        $form->text("name", "视频名称")
            ->rules("required");
//        $form->file2("url", "视频")
//            ->fileType('video/mp4')
//            ->options([
//                'dropZoneEnabled'         => false,
//                'uploadLabel'             => '上传',
//                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
//                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
//                'showUpload'              => true,
//                'initialPreviewFileType'  => 'video',
//                'uploadUrl'               => 'https://up-z2.qbox.me/',
//                'uploadExtraData'         => [
//                    'token' => $this->getUploadTokenInter('upload/video/'.$this->currentId),
//                ],
////                'allowedFileExtensions'=>['mp4','mpeg'],
//                'allowedFileExtensions'   => ['mp4'],
//                'maxFileCount'            => 1,
//            ])
//            ->rules("required")
////            ->rules("mimetypes:video/mp4")
//            ->help("视频只支持mp4格式文件,添加视频后需点击上传按钮上传,只能上传一个");

        $form->qiniuFile("url", "视频")
            ->options([
                'initialPreviewConfig'   => [
                    ['key' => 0, 'filetype' => 'video/mp4'],
                ],
                'initialPreviewFileType' => 'video',
                'allowedFileTypes'       => ['video'],
//                'dropZoneEnabled'         => false,
                'uploadLabel'             => '上传',
                'dropZoneTitle'          => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'              => 'https://up-z2.qbox.me/',
                'uploadExtraData'        => [
                    'token' => $this->getUploadTokenInter('upload/video/'.$this->currentId),
                ],
                'allowedFileExtensions'  => ['mp4'],
                'maxFileCount'           => 1, //同时上传的文件数量
            ])
            ->help("视频只支持mp4格式文件,添加视频后需点击上传按钮上传,只能上传一个");


        $form->display("show_url", "视频地址")->with(function () {
            return $this->url;
        });


    }
}
