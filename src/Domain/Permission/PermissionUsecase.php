<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Domain\Permission;

use Mallto\Admin\Data\Permission;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2019/1/2
 * Time: 12:05 PM
 */
class PermissionUsecase
{


    /**
     * 获取用户 某一权限的所有子权限
     *
     * @param        $adminUser
     * @param string $parentPermissionSlug
     *
     * @return mixed
     */
    public function getUserPermissionForModule($adminUser, $parentPermissionSlug = null)
    {

        //$parentPermissionIds = Permission::query()
        //    ->select('id')
        //    //->whereHas('roles', function ($query) use ($adminUser) {
        //    //    $query->whereHas('administrators', function ($query) use ($adminUser) {
        //    //        $query->where('id', $adminUser->id);
        //    //    });
        //    //})
        //    ->where("slug", $parentPermissionSlug)
        //    //->first();
        //    ->pluck('id')
        //    ->toArray();
        //
        //$parentPermissionIds = array_map(function ($id) {
        //    return "%." . $id . ".%";
        //}, $parentPermissionIds);
        //$parentPermissionIds = implode(",", $parentPermissionIds);
        //$parentPermissionIds = "('{" . $parentPermissionIds . "}')";
        //
        //return Permission::query()
        //    //->whereHas('roles', function ($query) use ($adminUser) {
        //    //    $query->whereHas('administrators', function ($query) use ($adminUser) {
        //    //        $query->where('id', $adminUser->id);
        //    //    });
        //    //})
        //    ->whereRaw("path like any $parentPermissionIds")
        //    //->select("id", "name", "slug", "path")
        //    ->select("id", "name", "slug")
        //    ->orderBy('order')
        //    //->get();
        //    ->pluck("name", "slug")->toArray();

        //去掉slug.之后的内容
        //$permissions = $subPermissions->map(function ($value, $key) {
        //    $tempSlug = explode(".", $value->slug)[0];
        //    $value->slug = $tempSlug;
        //
        //    return $value;
        //});

        //return $permissions->pluck("name", "slug")->toArray();

        //1. 查询用户拥有的权限
        $permissions = $adminUser->roles()
            ->with("permissions:id,name,slug,path")
            ->get()
            ->pluck('permissions')
            ->flatten();

        //2. 查询这些权限的子权限
        $ids = $permissions->pluck("id")->toArray();
        $ids = array_map(function ($id) {
            return "%." . $id . ".%";
        }, $ids);
        $ids = implode(",", $ids);
        $ids = "('{" . $ids . "}')";

        $subPermissions = Permission::whereRaw("path like any $ids")
            ->orderBy('order')
            ->select("id", "name", "slug", "path")
            ->get();
        $permissions = $subPermissions->merge($permissions);

        //3.查询用户权限中属于$parentPermission的
        //4.保留有(.)的权限
        $parentPermission = Permission::where("slug", $parentPermissionSlug)->first();

        $permissions = $permissions->filter(function ($value, $key) use ($parentPermission) {
            //if (str_contains($value->slug, ".")) {
            if ($parentPermission) {
                if (str_contains($value->path, ".$parentPermission->id.")) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
            //}
            //return false;
        });

        $permissions = $permissions->flatten();

        //去掉slug.之后的内容
        $permissions = $permissions->map(function ($value, $key) use ($parentPermission) {
            $tempSlug = explode(".", $value->slug)[0];
            $value->slug = $tempSlug;

            return $value;
        });

        //合并数据
        $permissions = $permissions->unique("slug");

        return $permissions->pluck("name", "slug")->toArray();

    }
}
