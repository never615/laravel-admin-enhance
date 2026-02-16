<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Permission\Api;

use Mallto\Location\Seeder\Permission\AdminApi\AdminApiPermissionBaseSeeder;

/**
 * 管理端接口权限生成
 *
 * Class AppSecretPermissionsSeeder
 *
 * @package Mallto\Location\Seeder\Permission
 */
class A0BasicPermissionSeeder extends AdminApiPermissionBaseSeeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {

        $managerId = $this->createPermissions('系统管理', ' system_permission_manager', false);
        $this->createPermissions('账号', 'admin_user', true, $managerId);
        $this->createPermissions('角色', 'admin_role', true, $managerId);
    }


}
