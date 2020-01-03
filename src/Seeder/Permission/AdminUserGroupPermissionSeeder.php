<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Permission;

use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\SeederMaker;

class AdminUserGroupPermissionSeeder extends Seeder
{

    use SeederMaker;


    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $parentId = 0;
        $parentPermisson = Permission::where("slug", "")->first();
        if ($parentPermisson) {
            $parentId = $parentPermisson->id;
        }
        $this->createPermissions("管理账户分组", "admin_user_groups", true, $parentId);
    }
}
