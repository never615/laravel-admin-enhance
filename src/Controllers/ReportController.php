<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Report;


class ReportController extends AdminCommonController
{

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "大数据报表";
    }

    protected function getIndexDesc()
    {
        return "管理";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Report::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->name();
        $grid->finish()->display(function ($finish) {
            return $finish == true ? "是" : "否";
        });

        //$url = $disk->privateDownloadUrl('folder/my_file.txt');
        $grid->status();
        $grid->column("download")->display(function () {
            if ($this->finish === true) {
                $disk = Storage::disk("qiniu_private");
                $url = $disk->privateDownloadUrl(config("app.unique").'/'.config("app.env").'/exports/'.$this->name,
                    60);

                return <<<EOT
                <a href="$url" target="_blank">点击下载</a>
EOT;
            } else {
                return "";
            }

        });
        $grid->subject()->name("主体");
        $grid->adminUser()->name("创建人");
        $grid->desc();
        $grid->created_at();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });


    }


    protected function formOption(Form $form)
    {
    }
    //todo 删除事件->删除对应文件


}
