<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Database\Eloquent\Collection;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Data\Subject;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleController extends AdminCommonController
{


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "角色管理";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Role::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->slug(trans('admin.slug'));
        $grid->name(trans('admin.name'));
        $grid->actions(function (Grid\Displayers\Actions $actions) {
//                if ($actions->row->slug == 'administrator') {
//                    $actions->disableDelete();
//                }
            //不能删除自己的角色
            if (Admin::user()->isRole($actions->row->slug) && Admin::user()->subject_id == $actions->row->subject_id) {
                $actions->disableDelete();
            }
        });
    }

    protected function formOption(Form $form)
    {
        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');

        $form->multipleSelect('permissions', trans('admin.permissions'))->options(function () {
            $subjectId = Admin::user()->subject_id;
//            $subjectId = $this->subject_id;
            if ($subjectId == 1) {
                $permissions = Permission::all();
            } else {
                //主体拥有的权限需要加上那几个公共功能模块的权限

                $permissions = new Collection();
                $permissionsTemp = Subject::find($subjectId)->permissions;
                $permissionsTemp = $permissionsTemp->merge(Permission::where("common", true)->get());

                $permissions = $permissions->merge($permissionsTemp);
                //查找子权限
                foreach ($permissionsTemp as $permission) {
                    //subPermissions
//                    \Log::info($permission->subPermissions());
                    $permissions = $permissions->merge($permission->subPermissions());
//                    $permissions = $permissions->merge(Permission::where("slug", 'like',
//                        "%$permission->slug%")->get());
                }

            }

            return Permission::selectOptions($permissions->toArray(), false, false);
        });


        $form->saving(function (Form $form) {
            $this->autoAdminUser($form);

            if ($form->slug == config("admin.roles.owner")) {
                throw new HttpException(403,"没有权限创建标识为owner的角色");
            }

            //已经存在的slug标识不能创建
            if ($form->slug && $form->slug != $form->model()->slug) {
                $tempSubjectId = $form->subject_id ?: $form->model()->subject_id;
                if (Role::where('slug', $form->slug)->where("subject_id", $tempSubjectId)->exists()) {
                    throw new HttpException(422,"标识:".$form->slug."已经存在,无法创建");
                }
            }
        });
    }
}
