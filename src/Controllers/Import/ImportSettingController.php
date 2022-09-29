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
        $grid->import_handler("模块处理类");

    }


    protected function formOption(Form $form)
    {
        $form->text("name", "模块名");

        $form->text("module_slug", "模块标识")
            ->rules("required")
            ->help('全局唯一,建议和导入模板的文件名一致和导入模块的路由名一致<br>
不建议通过管理端创建导入配置,推荐使用代码生成,避免所有项目部署都要手动配置一遍');
//            ->help('配置导入按钮ImportButton的管理模块页面路径的最后一段path,
//<br>如:https://xx.com/admin/member_cards.会员卡模块下,该标识就填写member_cards.
//<br>当 ImportButton 中没有传参时需要填写此项目,如: $tools->append(new ImportButton());
//');

        $form->text("import_handler", "模块处理类")
            ->rules("required")
            ->help('如:Mallto\Mall\Domain\Import\MemberCardImport,
<br>会通过resolve("Mallto\Mall\Domain\Import\MemberCardImport");获取对象,处理导入操作.
<br>该对象需要继承BaseImportHandler');

        $form->file("template_with_annotation_url", "导入模板")
            ->options([
                'allowedPreviewTypes'   => [],
                'allowedFileExtensions' => [ 'xls', 'xlsx' ],
            ]);

        //if ($this->currentId) {
        //    $form->displayE("show_template_with_annotation_url", "带说明的模板下载")
        //        ->with(function ($value) {
        //            $templateWithAnnotationUrl = $this->template_with_annotation_url;
        //            if ($templateWithAnnotationUrl) {
        //                if (starts_with($templateWithAnnotationUrl, "http")) {
        //                    $url = $templateWithAnnotationUrl;
        //                } else {
        //                    $url = config("app.file_url_prefix") . $templateWithAnnotationUrl;
        //                }
        //
        //                return '<a target="_blank" href="' . $url . '">点击下载示例模板</a>';
        //            }
        //        });
        //}
//        $form->file("template_url", "模板");
    }
}
