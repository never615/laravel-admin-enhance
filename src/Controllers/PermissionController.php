<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Tree;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Permission;

class PermissionController extends AdminCommonController
{


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "权限管理";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Permission::class;
    }

    protected function grid()
    {
        return Permission::tree(function (Tree $tree) {
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
        $form->select("parent_id", "父节点")->options(Permission::selectOptions());
        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');
        $form->switch("common", "基础功能权限")
            ->help("打开后,任何主体都默认拥有该权限对应的功能.即:在角色管理分配权限的时候可以进行分配");


        $form->saving(function ($form) {
            //创建/修改重新生成对应的path
            $parentId = $form->parent_id ?? $form->model()->parent_id;
            $parent = Permission::find($parentId);
            if ($parent) {
                if (!empty($parent->path)) {
                    $form->model()->path = $parent->path.$parent->id.".";
                } else {
                    $form->model()->path = ".".$parent->id.".";
                }
            }
        });

    }
}
