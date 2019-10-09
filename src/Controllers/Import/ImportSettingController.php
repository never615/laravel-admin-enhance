<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Import;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\ImportSetting;


/**
 * 导入配置
 * Class ImportSettingController
 *
 * @package Mallto\Admin\Controllers\Import
 */
class ImportSettingController extends AdminCommonController
{

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "导入配置";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return ImportSetting::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->name("模块说明");
        $grid->module_slug("模块标识");
        $grid->module_handler("模块处理类");

    }

    protected function formOption(Form $form)
    {
        $form->text("name", "模块说明");

        $form->text("module_slug", "模块标识")
            ->rules("required")
            ->help("配置导入按钮ImportButton的管理模块页面路径的最后一段path,
<br>如:https://xx.com/admin/member_cards.会员卡模块下,该标识就填写member_cards");

        $form->text("module_handler", "模块处理类")
            ->rules("required")
            ->help('如:\Mallto\Mall\Domain\Import\MemberCardImport,
<br>会通过resolve("\Mallto\Mall\Domain\Import\MemberCardImport");获取对象,处理导入操作.
<br>该对象需要继承BaseImportHandler');

        $form->file("template_with_annotation_url", "带说明的模板")
            ->options([
                'allowedPreviewTypes'   => [],
                'allowedFileExtensions' => ['xls', 'xlsx'],
            ]);
        if ($this->currentId) {
            $form->displayE("show_template_with_annotation_url", "带说明的模板下载")->with(function ($value) {
                $value = $this->template_with_annotation_url;
                if ($value) {
                    if (starts_with($value, "http")) {
                        $url = $value;
                    } else {
                        $url = config("app.file_url_prefix") . $value;
                    }

                    return '<a target="_blank" href="'.$url.'">点击下载示例模板</a>';
                }
            });
        }
//        $form->file("template_url", "模板");
    }
}
