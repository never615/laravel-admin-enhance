<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\Traits\PermissionHelp;
use Mallto\Tool\Exception\ResourceException;
use Mallto\Tool\Utils\AppUtils;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleController extends AdminCommonController
{

    use PermissionHelp;

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
//        $grid->slug(trans('admin.slug'));
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
        if (Admin::user()->isOwner()) {
            $form->display('slug', trans('admin.slug'));
        }

        $form->text('name', trans('admin.name'))->rules('required');

        $that = $this;
        $form->multipleSelect('permissions', trans('admin.permissions'))
            ->options(function () use ($that) {
                $subjectId = Admin::user()->subject_id;
                if ($subjectId == 1) {
                    $permissions = Permission::all()->toArray();
                } else {
                    //主体拥有的权限需要加上那几个公共功能模块的权限

                    $permissionsTemp = Subject::find($subjectId)->permissions;
                    $permissionsTemp = $permissionsTemp->merge(Permission::where("common", true)->get());
                    $permissions = $that->withSubPermissions($permissionsTemp);
                }

                return Permission::selectOptions($permissions, false, false);
            })
            ->help("权限有父子关系,若设置了父级权限则不用在设置子级权限.如:设置了用户管理,则无需在配置用户查看/用户删除/用户修改权限");


        $form->saving(function (Form $form) {
            if ($form->slug == config("admin.roles.owner")) {
                throw new HttpException(403, "没有权限创建标识为owner的角色");
            }


            //自动生成slug
            if ($form->name && $form->name != $form->model()->name) {
                //检查name,一个subject下不能重复
                if (Role::where("subject_id", $form->subject_id)
                    ->where("name", $form->name)
                    ->exists()) {
                    throw new ResourceException($form->name."已存在");
                }


                //处理slug
                //自动生成slug,同一个主体下不能重复
                $slug = pinyin_permalink($form->name);
                $slug = $this->generatorSlug($slug, $form->subject_id);
                $form->model()->slug = $slug;
            }

//            //已经存在的slug标识不能创建
//            if ($form->slug && $form->slug != $form->model()->slug) {
//                $tempSubjectId = $form->subject_id ?: $form->model()->subject_id;
//                if (Role::where('slug', $form->slug)->where("subject_id", $tempSubjectId)->exists()) {
//                    throw new HttpException(422, "标识:".$form->slug."已经存在,无法创建");
//                }
//            }
        });
    }


    /**
     * 检查是否有重复的slug
     *
     * @param $slug
     * @param $subjectId
     * @return string
     */
    private function generatorSlug($slug, $subjectId)
    {
        if (Role::where("subject_id", $subjectId)
            ->where("slug", $slug)
            ->exists()) {
            $slug = $slug."_".AppUtils::getRandomString(3);

            return $this->generatorSlug($slug, $subjectId);
        } else {
            return $slug;
        }
    }
}
