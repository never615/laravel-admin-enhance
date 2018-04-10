<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Seeder;

class Permission2TablesSeeder extends Seeder
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
        $permission = Permission::where("name", "文件管理")
            ->orWhere("name", "文件")->first();
        if ($permission) {
            return;
        }


        $this->createPermissions("文件", "uploads");

    }
}
