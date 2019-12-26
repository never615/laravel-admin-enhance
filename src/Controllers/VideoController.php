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

        $this->formVideo($form, "url", "video");

        $form->displayE("show_url", "视频地址")->with(function () {
            return $this->url;
        });


    }
}
