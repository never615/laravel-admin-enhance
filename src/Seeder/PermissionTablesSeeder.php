<?php

namespace Mallto\Admin\Seeder;


use Encore\Admin\Auth\Database\Permission;
use Illuminate\Database\Seeder;

class PermissionTablesSeeder extends Seeder
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

        $permission = Permission::where("name", "主体管理")
            ->orWhere("name", "主体")->first();
        if ($permission) {
            return;
        }


        /**
         * ------------------------  主体  ---------------------------
         */
        $this->createPermissions("主体", "subjects", true, 0, false, true);

        /**
         * ------------------------  账户  ---------------------------
         */
        $this->createPermissions("账户", "admins", true, 0, false, true);


        /**
         * ------------------------  角色  ---------------------------
         */
        $this->createPermissions("角色", "roles", true, 0, false, true);


        /**
         * ------------------------  权限  ---------------------------
         */
//        $this->createPermissions("权限", "permissions");


        /**
         * ------------------------  菜单  ---------------------------
         */
//        $this->createPermissions("菜单", "menus");

        /**
         * ------------------------  报表  ---------------------------
         */
        $this->createPermissions("报表", "reports", true, 0, false, true);

        /**
         * ------------------------  操作日志  ---------------------------
         */
//        $this->createPermissions("操作日志", "logs");
    }
}
