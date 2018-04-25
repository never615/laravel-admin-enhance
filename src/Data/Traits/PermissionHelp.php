<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Data\Traits;


trait PermissionHelp
{

    /**
     *
     * 获取一组权限的所有子权限,带着原来的这一组权限一起返回
     *
     * @param $permissions
     * @return array
     */
    public function withSubPermissions($permissions)
    {
        $tempPermissions = [];
        foreach ($permissions as $permission) {
            //查询权限的所有子权限
            $tempPermissions = array_merge($tempPermissions, $permission->subPermissions());
        }

        return array_merge($tempPermissions, $permissions->toArray());
    }

}
