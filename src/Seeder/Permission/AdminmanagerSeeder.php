<?php

namespace Mallto\Admin\Seeder\Permission;

use Illuminate\Database\Seeder;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Seeder\SeederMaker;

class AdminmanagerSeeder extends Seeder
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

        /**
         * ------------------------  主体  ---------------------------
         */
        $this->createPermissions('主体', 'subjects', true, 0,
            false, false, false, [
                'index' => '查看',  //列表页/详情页/show
                'create' => '创建', //创建页/保存
                'update' => '修改', //修改
                'destroy' => '删除', //删除权限
            ]);

        /**
         * ------------------------  账户  ---------------------------
         */
        $parentId = $this->createPermissions('账户', 'admins', true, 0, false, true);

        /**
         * ------------------------  角色  ---------------------------
         */
        $this->createPermissions('角色', 'roles', true, 0, false, true);

        /**
         * ------------------------  权限  ---------------------------
         */
        $this->createPermissions('权限', 'permissions');

        $this->createPermissions('管理端API权限', 'api_permissions');

        /**
         * ------------------------  菜单  ---------------------------
         */
        $this->createPermissions('菜单', 'menus');

        $this->createPermissions('前端菜单', 'front_menus');


        /**
         * ------------------------  报表  ---------------------------
         */
        $this->createPermissions('报表', 'reports', true, 0, false, false);

        /**
         * ------------------------  操作日志  ---------------------------
         */
        $this->createPermissions('操作日志', 'logs');

        $this->createPermissions('项目配置', 'subject_settings', true, 0, false);

//        $this->createPermissions('文件', 'uploads');

        $this->createPermissions('视频', 'videos');

        $this->createPermissions('数据看板', 'dashboard', false, 0, true);

        Permission::query()
            ->where('slug', 'Login_users')
            ->delete();

        $this->createPermissions('在线账号', 'login_users');
    }
}
