<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Subject;

/**
 * 处理subject的显示和自动保存
 * Class AdminSubjectTrait
 *
 * @package Mallto\Admin\Controllers\Base
 */
trait AdminSubjectTrait
{

    /**
     * grid 中控制subject的显示
     *
     * @param $grid
     */
    protected function gridSubject($grid)
    {
        if (Schema::hasColumn($this->tableName, "subject_id")) {
            //拥有子主体的主体,在table中显示条目的所属主体
            if (Admin::user()->subject->hasChildrenSubject()) {
                $grid->subject_id()->sortable()->display(function ($value) {

                    $subject = AdminUtils::getSubject($value);

                    return $subject->name ?? "已删除";
                });
            }
        }
    }


    /**
     * form 主体的设置显示
     *
     * @param $form
     */
    protected function formSubject($form)
    {
        if (Schema::hasColumn($this->tableName, "subject_id")) {
            //项目拥有者任何时候都可以编辑选择主体,即便是启用了自动设置主体
            if (\Mallto\Admin\AdminUtils::isOwner()) {
                $form->selectE("subject_id", "主体")
                    ->options(
                        Subject::whereNotNull("uuid")
                            ->orderBy('id', 'desc')
                            ->pluck("name", "id")
                    )
                    ->rules("required");
            } else {
//                $form->displayE("subject.name", "主体");
//                $form->hideFieldsByCreate("subject.name");
            }
        }
    }


    /**
     * 自动设置subjectId
     * 在不是项目拥有者的情况下
     * 只要model的表中有subject_id字段,就会自动设置subject_id,
     * subject_id设置为当前账号所属的基主体,即自己或者父主体中总部设置有打开的(对应数据表中的base字段)
     *
     *
     * 管理端编辑的对象不能使用basemodel的自动设置subject_id,
     * 因为管理端的saving方法可能会使用当前编辑对象的subject_id设置值.
     * 而form->saving方法是在调用下面方法之前调用的
     *
     * @param $form
     *
     * @return
     */
    protected function autoSubjectSaving($form)
    {
        //不是项目拥有者才自动设置subject_id
        if (Schema::hasColumn($this->tableName, "subject_id") &&
            ! \Mallto\Admin\AdminUtils::isOwner()) {
            //项目拥有者任何时候都可以编辑选择主体,即便是启用了自动设置主体
            //什么账号创建就是谁的总部的
            if ( ! $this->adminUser) {
                $adminUser = Admin::user();
                $this->adminUser = $adminUser;
            } else {
                $adminUser = $this->adminUser;
            }
            $subject = $adminUser->subject;
            $baseSubject = $subject->baseSubject();
            if ($baseSubject && $baseSubject->base) {
                $form->subject_id = $baseSubject->id;
                $form->model()->subject_id = $baseSubject->id;
            } else {
                $form->subject_id = $subject->id;
                $form->model()->subject_id = $subject->id;
            }
        }

    }
}
