<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;


use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\Administrator;

/**
 * 处理管理端操作者的显示和自动保存
 * Class AdminUserTrait
 *
 * @package Mallto\Admin\Controllers\Base
 */
trait AdminUserTrait
{

    /**
     * form 的admin_user_id设置
     *
     * @param $form
     */
    protected function formAdminUser($form)
    {
        if (Schema::hasColumn($this->tableName, "admin_user_id")) {
            $form->display('admin_user_id', "操作人")->with(function ($value) {
                $adminUser = Administrator::find($value);

                return $adminUser ? $adminUser->name : "";
            });

            $form->hideFieldsByCreate(["admin_user_id"]);
        }
    }

    /**
     * 自动设置adminUser
     * 只要model的表中有admin_user_id字段,就会自动设置管理端的操作人
     *
     * @param $form
     */
    protected function autoAdminUserSaving($form)
    {
        if (Schema::hasColumn($this->tableName, "admin_user_id")) {
            $adminUser = Admin::user();
            $form->model()->admin_user_id = $adminUser->id;
        }

    }

}
