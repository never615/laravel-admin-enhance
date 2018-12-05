<?php

namespace Mallto\Admin\Seeder\Permission;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\SeederMaker;

class AdminmanagerSeeder extends Seeder
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

        /**
         * ------------------------  主体  ---------------------------
         */
        $this->createPermissions("主体", "subjects", true, 0,
            false, true, false, [
                "index"   => "查看",  //列表页/详情页/show
                "create"  => "创建", //创建页/保存
                "update"  => "修改", //修改
                "destroy" => "删除", //删除权限
            ]);

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
        $this->createPermissions("权限", "permissions");


        /**
         * ------------------------  菜单  ---------------------------
         */
        $this->createPermissions("菜单", "menus");

        /**
         * ------------------------  报表  ---------------------------
         */
        $this->createPermissions("报表", "reports", true, 0, false, false);

        /**
         * ------------------------  操作日志  ---------------------------
         */
//        $this->createPermissions("操作日志", "logs");

//        $this->createPermissions("文件", "uploads");

        $this->createPermissions("视频", "videos");

    }
}
