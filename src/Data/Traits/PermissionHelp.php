<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;

use Mallto\Admin\Data\Permission;

trait PermissionHelp
{

    /**
     *
     * 获取一组权限的所有子权限,带着原来的这一组权限一起返回
     *
     * @param $permissions
     *
     * @return array
     */
    public function withSubPermissions($permissions)
    {
        $ids = $permissions->pluck("id")->toArray();
        $ids = array_map(function ($id) {
            return "%." . $id . ".%";
        }, $ids);
        $ids = implode(",", $ids);
        $ids = "('{" . $ids . "}')";

        $tempPermissions = Permission::
        whereRaw("path like any $ids")
            ->get()
            ->toArray();

        return array_merge($tempPermissions, $permissions->toArray());

//        $tempPermissions = [];
//        foreach ($permissions as $permission) {
//            //查询权限的所有子权限
//            $tempPermissions = array_merge($tempPermissions, $permission->subPermissions());
//        }
//
//        return array_merge($tempPermissions, $permissions->toArray());
    }

}
