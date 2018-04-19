<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class SubjectController extends AdminCommonController
{
    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "主体";
    }

    /**
     * 获取这个模块的Model
     *o
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Subject::class;
    }

    protected function gridOption(Grid $grid)
    {
        $grid->name()->sortable();
        $grid->parent_id("归属")->display(function ($parent_id) {
            $subject = Subject::find($parent_id);
            if ($subject) {
                return $subject->name;
            } else {
                if ($parent_id == 0) {
                    return "项目开发商";
                } else {
                    return "";
                }

            }
        })->sortable();

        if (Admin::user()->isOwner()) {
            $grid->uuid()->editable();
        }


        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike("name");
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if (Admin::user()->subject->id == $actions->row->id) {
                $actions->disableDelete();
            }

        });

    }

    protected function formOption(Form $form)
    {
        $form->tab("基本信息", function ($form) {

            $form->text("name")->rules('required');
            $form->image("logo")
                ->uniqueName()
                ->move('subject/logo/'.$this->currentId);

        })->tab("系统必要配置", function ($form) {

            //父级主体和已购模块只能父级设置,自己可以看,不能改
            $current = Subject::find($this->currentId);
            $parent = null;
            if ($current) {
                $parent = Subject::find($current->parent_id);
            }

            $form->select("parent_id", "父级主体")->options(function () use ($parent) {
                if ($this->id == 1) {
                    $arr = Subject::pluck('name', 'id');
                    array_add($arr, 0, "项目开发商");
                } else {
                    //返回自己有权限查看的和自己已经配置的
                    $arr = Subject::dynamicData()->pluck("name", "id");
                    if ($parent) {
                        array_add($arr, $parent->id, $parent->name);
                    }
                }


                return $arr;
            })->rules("required");

            if (Admin::user()->isOwner()) {
                $permissions = Permission::where("parent_id", 0)->where("common", false)->get();
            } else {
                $permissions = Admin::user()->subject->baseSubject()->permissions()
                    ->where("common", false)
                    ->where("parent_id", 0)
                    ->get();
            }

            $form->divider();

            if (Admin::user()->isOwner()) {
                $form->multipleSelect('permissions', "已购模块")
                    ->options(Permission::selectOptions($permissions->toArray(),
                        false,
                        false));
                $form->display('sms_count', "消费短信数");
                $form->text("uuid", "主体唯一标识");
                $form->switch("base", "总部");
                $form->textarea("extra_config", "其他配置")
                    ->help("以文本的形式配置,方便动态修改.格式:json");
            }

        })->tab("其他配置", function ($form) {
            if (Admin::user()->isOwner()) {
                $form->hasMany("subjectconfigs", "",function (Form\NestedForm $form) {
                    $form->select("type")
                        ->options(SubjectConfig::TYPE);
                    $form->text("key");
                    $form->text("value");
                    $form->text("remark");
                });
            }
        });


        $form->saving(function (Form $form) {
            if (!Admin::user()->isOwner()) {
                //修改的是自己或者是自己的父级
                $currentSubject = Admin::user()->subject;
                $parentSubjects = $currentSubject->getParentSubjects()->toArray();
                $parentSubjectIds = [];
                if (!empty($parentSubjects)) {
                    $parentSubjectIds = array_pluck($parentSubjects, 'id');
                }

                if (Admin::user()->subject_id == $form->model()->id || in_array($form->model()->id,
                        $parentSubjectIds)
                ) {
                    if ($form->permissions) {
                        $tempPermissions = $form->permissions;

                        $tempPermissions = array_filter($tempPermissions, function ($value) {
                            if (!is_null($value)) {
                                return $value;
                            }
                        });

                        $oldPermissions = $form->model()->permissions->pluck("id")->toArray();

                        if (($form->permissions &&
                                (!empty(array_diff($tempPermissions,
                                        $oldPermissions)) || !empty(array_diff($oldPermissions,
                                        $tempPermissions))))
                            ||
                            ($form->parent_id && $form->model()->parent_id != $form->parent_id)
                        ) {
                            throw new AccessDeniedHttpException("无权修改主体拥有的功能或父级主体,请联系上级管理.");
                        }
                    }
                }
            }

            //父主体为顶级,即项目拥有者的主体,不能被修改
            if ($form->model()->parent_id === 0) {
                $form->parent_id = 0;
            }

            //父主体修改检查,不能设置为本身,不能设置为孩子
            if ($form->parent_id && $form->parent_id != $form->model()->parent_id) {
                $currentSubject = Subject::find($this->currentId);

                if ($currentSubject) {
                    if ($form->parent_id == $currentSubject->id) {
                        throw new HttpException(422,"不能设置自己为自己的父主体");
                    }

                    $childIds = $currentSubject->getChildrenSubject();

                    if (in_array($form->parent_id, $childIds)) {
                        throw new HttpException(422,"不能设置子级主体为自己的父主体");
                    }
                }
            }
        });
    }
}
