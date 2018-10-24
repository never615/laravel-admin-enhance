<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Import;


use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\ImportRecord;
use Mallto\Admin\Data\ImportSetting;
use Mallto\Admin\Jobs\ImportFileJob;


/**
 * 导入记录
 * Class ImportRecordController
 *
 * @package Mallto\Admin\Controllers\Import
 */
class ImportRecordController extends AdminCommonController
{

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "数据导入";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return ImportRecord::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->setting()->name("模块");
        $grid->status()->display(function ($value) {
            return $value ? ImportRecord::STATUS[$value] : "";
        });

        $grid->failure_reason();

        $grid->actions(function(Grid\Displayers\Actions $actions){
            $actions->disableEdit();
            $actions->disableView();
        });
//        $grid->disableActions();
//        $grid->disableCreateButton();
    }

    protected function formOption(Form $form)
    {
        $moduleSlug = request("module_slug");

        if ($moduleSlug) {
            $form->hidden("module_slug")
                ->default($moduleSlug);

            $importSetting = ImportSetting::where("module_slug", $moduleSlug)
                ->first();
            if ($importSetting) {
                $form->display("template_url", "导入模板示例")->with(function () use ($importSetting) {
                    $url = config("app.file_url_prefix").$importSetting->template_with_annotation_url;

                    return '<a target="_blank" href="'.$url.'">点击下载示例模板</a>';
                });
            }
        } else {
            $form->select("module_slug", "模块")
                ->options(ImportSetting::selectSourceDatas());
        }


        $form->filePrivate("file_url", "文件")
            ->move(Admin::user()->id.'/import_file')
            ->help("导入的数据一次不建议超过三万,否则可能失败");


        $form->saving(function ($form) {


        });

        $form->saved(function ($form) {
            dispatch(new ImportFileJob($form->model()->id));

        });
    }
}
