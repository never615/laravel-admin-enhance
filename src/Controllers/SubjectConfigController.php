<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\SubjectConfig;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/12/7
 * Time: 2:53 PM
 */
class SubjectConfigController extends AdminCommonController
{

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return SubjectConfig::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->key()->display(function ($value) {
            return config("app.subject_config_key")[$value] ?? $value;
        });
        $grid->value()->limit(100);

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike("key");
        });
    }

    /**
     * 需要实现的form设置
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @param Form $form
     * @return mixed
     */
    protected function formOption(Form $form)
    {
        $form->select("type")
            ->options(SubjectConfig::TYPE)
            ->default("private");

        $form->displayE("show_default_key","预设的一些key")
            ->with(function($values){
                $html='<table border="1"><tr><th>说明</th><th>key</th></tr>';
               foreach (config("app.subject_config_key") as $key=>$value){
                   $html.="<tr><th>$value</th><th>$key</th></tr>";
//                   $html.=' <tr>'.$value.":".$key."</tr>";
               }

               return $html."</table>";
            });

        $form->text("key");

        $form->textarea("value")->rows(15);
        $form->textarea("remark");
    }
}