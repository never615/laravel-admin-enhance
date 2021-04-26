<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\Traits\PermissionHelp;
use Mallto\Tool\Domain\Traits\SlugAutoSave;
use Mallto\Tool\Exception\ResourceException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleController extends AdminCommonController
{

    use PermissionHelp, SlugAutoSave;


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return '角色管理';
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
            //不能删除自己的角色
            if (Admin::user()->isRole($actions->row->slug) && Admin::user()->subject_id == $actions->row->subject_id) {
                $actions->disableDelete();
            }
            $actions->disableView();
        });

        if (AdminUtils::isOwner()) {
            $grid->column('down', '角色权限')->display(function ($value) {
                return "<button type='button' class='btn btn-sm btn-twitte'>
            <i class='fa fa-download'></i>
            <span>导出角色权限</span>
    </button>";
            })->linkE(function () {
                if ($this->row->id) {
                    return '/admin/auth/permissions?_export_=all&role_id=' . $this->row->id;
                }
            });
        }

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike('name', '角色名');
        });
    }


    protected function formOption(Form $form)
    {
//        if (\Mallto\Admin\AdminUtils::isOwner()) {
//            $form->text('slug', trans('admin.slug'))
//                ->help('不填写会自动生成,建议不填写');
//        }

        $form->text('name', trans('admin.name'))
            ->rules('required')
            ->help('权限有父子关系,若设置了父级权限则不用在设置子级权限.如:设置了用户管理,则无需在配置用户查看/用户删除/用户修改权限');

        $that = $this;
//        $form->multipleSelect('permissions', trans('admin.permissions'))
//        $form->listbox('permissions', trans('admin.permissions'))
        $form->checkbox('permissions', trans('admin.permissions'))
            ->options(function () use ($that) {
                $subjectId = Admin::user()->subject_id;
                if ($subjectId == 1) {
                    $permissions = Permission::orderBy('order')->get()->toArray();
                } else {
                    $permissionsTemp = Subject::find($subjectId)
                        ->permissions()
                        ->orderBy('order')
                        ->get();

                    //主体拥有的权限需要加上那几个公共功能模块的权限
                    $permissionsTemp = $permissionsTemp->merge(
                        Permission::where('common', true)->get()
                    );
                    $permissions = $that->withSubPermissions($permissionsTemp);
                }

                //因为分配的主体已购模块包含parent_id不是0的,所以在此处显示这部分权限,需要配置下parentId
                return Permission::selectOptions($permissions, false, false,
                    (isset($permissionsTemp) ? array_unique($permissionsTemp->pluck('parent_id')->toArray()) : 0));
            })
//            ->settings([
//                'selectorMinimalHeight'   => 500,
//                'preserveSelectionOnMove' => false,
//            ])
            ->stacked()
            ->help('权限有父子关系,若设置了父级权限则不用在设置子级权限.如:设置了用户管理,则无需在配置用户查看/用户删除/用户修改权限');

        $form->saving(function (Form $form) {
            if ($form->slug == config('admin.roles.owner')) {
                throw new HttpException(403, '没有权限创建标识为owner的角色');
            }
            if ( ! AdminUtils::isOwner() && $form->model()->slug == 'admin') {
                throw new ResourceException('非项目拥有者不能编辑该角色');
            }
            $this->slugSavingCheck($form);
        });

        $form->saved(function ($form) {
            AdminUtils::clearMenuCache();
        });
    }

}
