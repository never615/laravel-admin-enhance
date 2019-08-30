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
use Mallto\Tool\Exception\PermissionDeniedException;


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

        $grid->failure_reason()->display(function ($value) {
            return str_limit($value, 30);
        });

        $grid->finish_at("完成时间");

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });
    }

    protected function formOption(Form $form)
    {
        if ($this->currentId) {
            $form->displayE("setting.name", "模块");

            $form->displayE("status")->with(function ($value) {
                return ImportRecord::STATUS[$value] ?? "";
            });
            $form->displayE("failure_reason");
            $form->displayE("finish_at", "完成时间");

            $form->displayE("remark");

            $form->footer(function (Form\Footer $footer) {
                $footer->disableSubmit();
                $footer->disableReset();
            });
        } else {
            $moduleSlug = request("module_slug");

            if ($moduleSlug) {

                $form->hidden("module_slug")
                    ->default($moduleSlug);

                $form->display("module_slug_display", "模块")
                    ->default($moduleSlug)
                    ->with(function ($value) use ($moduleSlug) {
                        return ImportSetting::where("module_slug", $moduleSlug)
                                ->first()->name ?? $value;
                    });

                $importSetting = ImportSetting::where("module_slug", $moduleSlug)
                    ->first();
                if ($importSetting && $importSetting->template_with_annotation_url) {
                    $form->display("template_url", "导入模板示例")
                        ->with(function () use ($importSetting) {
                            $url = config("app.file_url_prefix").$importSetting->template_with_annotation_url;

                            return '<a target="_blank" href="'.$url.'">点击下载示例模板</a>';
                        });
                }
            } else {
                $form->select("module_slug", "模块")
                    ->rules("required")
                    ->options(ImportSetting::selectSourceDatas());
            }


            $form->filePrivate("file_url", "文件")
                ->options([
                    'allowedPreviewTypes'   => [],
                    'allowedFileExtensions' => ['xls', 'xlsx'],
                ])
                ->rules("required")
                ->move(Admin::user()->id.'/import_file')
                ->help("导入文件只能保留一个工作表");


            $this->formExtraConfig($form);

            $form->textarea("remark");
        }


        $form->saving(function ($form) {
            if ($this->currentId) {
                throw new PermissionDeniedException("非法提交");
            }
        });


        $form->saved(function ($form) {
            dispatch(new ImportFileJob($form->model()->id));
        });
    }

    /**
     * 额外的导入配置
     *
     * @param $form
     */
    protected function formExtraConfig($form)
    {
//        $form->embeds("extra", "其他配置", function (EmbeddedForm $form) {
//
//        });
    }


}
