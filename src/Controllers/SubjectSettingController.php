<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\Exception\SubjectConfigException;
use Mallto\Admin\Facades\AdminE;

/**
 * Class SubjectSettingController.
 */
class SubjectSettingController extends AdminCommonController
{

    protected function title()
    {
        return '项目配置';
    }


    /**
     * 获取这个模块的Model.
     *
     * @return mixed
     */
    protected function getModel()
    {
        return SubjectSetting::class;
    }


    protected function gridOption(Grid $grid)
    {
    }


    /**
     * 其他库添加的扩展配置
     *
     * @var array
     */
    public $expandSettingHandlers = [];


    /**
     * 需要实现的form设置.
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @return mixed
     */
    protected function defaultFormOption(Form $form)
    {
        $form->tab('基本配置', function (Form $form) {

            $form->multipleSelect('front_column', '前端可以请求的列')
                ->options(array_combine(Schema::getColumnListing('subject_settings'),
                    Schema::getColumnListing('subject_settings')))
                ->help('配置在这里前端才有权限请求');
            $form->multipleSelect('file_type_column', '文件类型的列')
                ->options(array_combine(Schema::getColumnListing('subject_settings'),
                    Schema::getColumnListing('subject_settings')))
                ->help('配置在这里的列前端请求的时候会自动加文件前缀');

            $this->formSubject($form);
            $this->formAdminUser($form);
            $form->displayE('created_at', trans('admin.created_at'));
            $form->displayE('updated_at', trans('admin.updated_at'));
        });

        //初始化其他库添加的subject配置对象
        $subjectSettingExpands = AdminE::getSubjectSettingClass();

        foreach ($subjectSettingExpands as $subjectSettingExpand) {
            $expandSettingHandler = app($subjectSettingExpand);
            $this->expandSettingHandlers[] = $expandSettingHandler;

            $expandSettingHandler->extend($form, $this->currentId);
        }

        $form->saving(function ($form) {
            $this->autoSubjectSaving($form);
            $this->autoAdminUserSaving($form);
            $adminUser = Admin::user();

            if ( ! $this->currentId) {
                $subjectSettingExists = SubjectSetting::query()
                    ->where('subject_id', $form->subject_id)
                    ->exists();

                if ($subjectSettingExists) {
                    throw new SubjectConfigException('该主体已有配置，请勿新增');
                }
            }

            foreach ($this->expandSettingHandlers as $expandSettingHandler) {
                $expandSettingHandler->formSaving($form, $adminUser);
            }
        });

        $form->saved(function ($form) {
            $adminUser = Admin::user();

            foreach ($this->expandSettingHandlers as $expandSettingHandler) {
                $expandSettingHandler->formSaved($form, $adminUser);
            }
        });
    }


    /**
     * 需要实现的form设置.
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @return mixed
     */
    protected function formOption(Form $form)
    {
    }
}
