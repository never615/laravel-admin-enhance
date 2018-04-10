<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Seeder;

class VideoPermissionSeeder extends Seeder
{

    use SeederMaker;

    protected $order = 0;


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = Permission::where("name", "视频管理")
            ->where("name", "视频s")->first();
        if ($permission) {
            return;
        }

        $this->createPermissions("视频", "videos");

    }
}
