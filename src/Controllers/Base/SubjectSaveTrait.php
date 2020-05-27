<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Mallto\Admin\Data\Subject;
use Mallto\Tool\Exception\ResourceException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/5
 * Time: 下午12:13
 */
trait SubjectSaveTrait
{

    /**
     * 修改是检查权限
     *
     * @param $adminUser
     * @param $form
     */
    private function saving($adminUser, $form)
    {
        if ( ! $adminUser->isOwner()) {
            //修改的是自己或者是自己的父级
            $currentSubject = $adminUser->subject;

            $parentSubjectIds = $currentSubject->getParentSubjectIds();

            if ($adminUser->subject_id == $form->model()->id || in_array($form->model()->id,
                    $parentSubjectIds)
            ) {
                if ($form->permissions) {
                    $tempPermissions = $form->permissions;

                    //提交过来的数组id,有一个null总是,过滤掉
                    $tempPermissions = array_filter($tempPermissions, function ($value) {
                        if ( ! is_null($value)) {
                            return $value;
                        }
                    });

                    $oldPermissions = $form->model()->permissions->pluck("id")->toArray();

                    if (($form->permissions && ( ! empty(array_diff($tempPermissions,
                                    $oldPermissions)) || ! empty(array_diff($oldPermissions,
                                    $tempPermissions)))
                        )
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
                    throw new ResourceException("不能设置自己为自己的父主体");
                }

                $childIds = $currentSubject->getChildrenSubject();

                if (in_array($form->parent_id, $childIds)) {
                    throw new ResourceException("不能设置子级主体为自己的父主体");
                }
            }
        }

        //添加新创建的subject的path字段,用于加快查询速度
        $parentId = $form->parent_id ?? $form->model()->parent_id;
        $parent = Subject::find($parentId);
        if ($parent) {
            if ($parent && ! empty($parent->path)) {
                $form->model()->path = $parent->path . $parent->id . ".";
            } else {
                $form->model()->path = "." . $parent->id . ".";
            }
        }

    }


    ///**
    // *
    // * 创建主体的时候自动创建该主体的管理员角色
    // *
    // * 同时赋予该主体的已购权限
    // * 更新是也跟随更新
    // *
    // * @param $form
    // */
    //protected function createOrUpdateAdminRole($form)
    //{
    //    $uuid = $form->uuid ?? $form->model()->uuid;
    //    if ($uuid) {
    //        $subjectId = $form->model()->id;
    //        $name = $form->model()->name;
    //
    //        //创建角色
    //        $adminRole = Role::firstOrCreate([
    //            "subject_id" => $subjectId,
    //            "slug"       => "admin",
    //        ], [
    //            "name" => $name . "管理员",
    //        ]);
    //
    //        //给角色分配权限
    //        if ($form->permissions) {
    //            $permissionIds = $form->permissions;
    //
    //            //提交过来的数组id,有一个null总是,过滤掉
    //            $permissionIds = array_filter($permissionIds, function ($value) {
    //                if ( ! is_null($value)) {
    //                    return $value;
    //                }
    //            });
    //
    //            //$permissionIds添加上base权限
    //            $basePermissions = Permission::where("common", true)
    //                ->pluck("id")
    //                ->toArray();
    //
    //            $permissionIds = array_merge($permissionIds, $basePermissions);
    //
    //            //把subject的已购权限分配到该主体的管理员账号上
    //            $adminRole->permissions()->sync($permissionIds);
    //
    //            AdminUtils::clearMenuCache();
    //        }
    //
    //        if ( ! Administrator::where("subject_id", $subjectId)
    //            ->exists()) {
    //            $adminUser = Administrator::firstOrCreate([
    //                "subject_id"     => $subjectId,
    //                "adminable_id"   => $subjectId,
    //                "adminable_type" => "subject",
    //                "username"       => implode("", pinyin($name)),
    //                "name"           => $name . "管理",
    //                "password"       => bcrypt(implode("", pinyin($name))),
    //            ]);
    //            $adminUser->roles()->sync($adminRole->id);
    //        }
    //    }
    //}
}
