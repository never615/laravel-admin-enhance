<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Traits;

use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Data\Role;
use Mallto\Tool\Exception\ResourceException;

trait  AdminPermissionDistribution
{

    /**
     * 创建角色并且分配权限
     * $name 角色名称
     * $permission 角色权限名称数组集
     */
    public function createAssignRole($subjectId, $name, $permission = [])
    {
        $adminRole = Role::query()
            ->where('name', $name)
            ->first();
        //if ( ! $adminRole) {
        //    //没有则创建角色
        //    $adminRole = Role::query()->create(
        //        [
        //            'name'       => $name,
        //            'slug'       => implode('-', pinyin($name)),
        //            'subject_id' => $subjectId,
        //        ]);
        //    if (isset($permission)) {
        //        //添加对应权限
        //        $createPermission = Permission::query()->whereIn('name', $permission)->pluck('id')->toArray();
        //        $adminRole->permissions()->sync($createPermission);
        //    }
        //}

        return $adminRole;
    }


    /**
     * 创建账号分配角色
     * $name 角色名称
     * $permission 角色权限名称数组集
     */
    public function createAdminUserRole($subjectId, $adminUserArr, $adminRoleId = null)
    {
        if ( ! isset($adminUserArr['username'])) {
            throw new ResourceException('用户名(账号)不能为空');
        }

        $adminUser = Administrator::where('subject_id', $subjectId)
            ->where('username', $adminUserArr['username'])
            ->first();

        $username = $adminUserArr['username'];
        $possword = $adminUserArr['password'] ?? $username;
        $name = $adminUserArr['name'] ?? $username;
        $mobile = $adminUserArr['mobile'] ?? null;
        if ( ! $adminUser) {
            $adminUser = Administrator::firstOrCreate([
                'subject_id'     => $subjectId,
                'adminable_id'   => $subjectId,
                'adminable_type' => 'subject',
                'username'       => $username,
                'name'           => $name,
                'mobile'         => $mobile,
                'password'       => bcrypt($possword),
            ]);

            if (isset($adminRoleId)) {
                $adminUser->roles()->sync($adminRoleId);
            }
        }

        return $adminUser;
    }
}
