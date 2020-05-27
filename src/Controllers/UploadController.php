<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Controllers\Base\QiniuToken;
use Mallto\Admin\Data\Upload;

/**
 * 上传文件管理
 * Class UploadController
 *
 * @package Encore\Admin\Controllers
 */
class UploadController extends AdminCommonController
{

    use QiniuToken;


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "文件管理";
    }


    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Upload::class;
    }


    protected function gridOption(Grid $grid)
    {
        $grid->desc("文件描述");
        $grid->column('url', "文件地址")->urlWrapper();
    }


    protected function formOption(Form $form)
    {
        $form->text("desc", "文件描述");

        $form->qiniuFile("url", "文件")
            ->options([
                'uploadLabel'             => '上传',
                'dropZoneTitle'           => '拖拽文件到这里 &hellip;',
                'msgInvalidFileExtension' => '不正确的文件扩展名 "{name}". 只支持 "{extensions}" 的文件扩展名.',
                'showUpload'              => true,
                'uploadUrl'               => 'https://up-z2.qbox.me/',
                'uploadExtraData'         => [
                    'token' => $this->getUploadTokenInter('upload/file/' . $this->currentId),
                ],
                'maxFileCount'            => 1, //同时上传的文件数量
            ])
            ->help("添加文件后请点击上传按钮");

        $form->displayE("show_url", "文件地址")->with(function () {
            return $this->url;
        });

    }
}
