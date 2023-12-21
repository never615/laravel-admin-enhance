<?php
/**
 * Copyright (c) 2023. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Tree;
use Mallto\Admin\CacheUtils;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Admin\Data\Role;

class ApiPermissionController extends AdminCommonController
{

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "管理端API权限管理";
    }


    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return AdminApiPermission::class;
    }


    protected function grid()
    {
        if (request('_export_') == 'all') {
            $grid = new Grid(new AdminApiPermission());

            if (request('role_id')) {
                $role = Role::query()->findOrFail(request('role_id'));

                $subPermissions = [];
                foreach ($role->permissions as $permission) {
                    $temps = $permission->subPermissions();

                    $subPermissions[] = $permission->toArray()['slug'];

                    if (!empty($temps)) {
                        foreach ($temps as $temp) {
                            $subPermissions[] = $temp['slug'];
                        }
                    }
                }
            }

            $grid->model()->whereIn('slug', $subPermissions);

            $grid->disablePagination();
        }

        return AdminApiPermission::tree(function (Tree $tree) {
            $tree->branch(function ($branch) {
                $payload = "<strong>{$branch['name']}</strong>";

                return $payload;
            });
        });
    }


    protected function gridOption(Grid $grid)
    {

    }


    protected function formOption(Form $form)
    {
        $form->select("parent_id", "父节点")->options(AdminApiPermission::selectOptions());
        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');
        $form->switch("common", "基础功能权限")
            ->help("打开后,任何主体都默认拥有该权限对应的功能.即:在角色管理分配权限的时候可以进行分配");

        $form->saving(function ($form) {
            //创建/修改重新生成对应的path
            $parentId = $form->parent_id ?? $form->model()->parent_id;
            $parent = AdminApiPermission::find($parentId);
            if ($parent) {
                if (!empty($parent->path)) {
                    $form->model()->path = $parent->path . $parent->id . ".";
                } else {
                    $form->model()->path = "." . $parent->id . ".";
                }
            }
        });

        $form->saved(function ($form) {
            CacheUtils::clearMenuCache();
        });

    }
}
