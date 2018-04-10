<?php

namespace Mallto\Admin\Seeder\Menu;


use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $order = 100;

        //2000+ 是管理

        //10000+ 是辅助功能

        $menu = Menu::whereIn("title", ["控制面板", "Dashboard"])->first();
        if ($menu) {
            return;
        }


        /**
         * --------------------------------  Menu create   -----------------------------------
         */
        Menu::insert([
            [
                'parent_id' => 0,
                'order' => $order += 1,
                'title' => '控制面板',
                'icon' => 'fa-bar-chart',
                'uri' => 'dashboard',
            ],
            [
                'parent_id' => 0,
                'order' => $order = 2000,
                'title' => '管理',
                'icon' => 'fa-tasks',
                'uri' => 'manager',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '主体',
                'icon' => 'fa-shopping-bag',
                'uri' => 'subjects.index',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '账户',
                'icon' => 'fa-users',
                'uri' => 'admins.index',

            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '角色',
                'icon' => 'fa-user',
                'uri' => 'roles.index',

            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '权限',
                'icon' => 'fa-user',
                'uri' => 'permissions.index',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '菜单',
                'icon' => 'fa-bars',
                'uri' => 'menus.index',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '报表管理',
                'icon' => 'fa-table',
                'uri' => 'reports.index',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '操作日志',
                'icon' => 'fa-history',
                'uri' => 'logs.index',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '文件管理',
                'icon' => 'fa-file',
                'uri' => 'uploads.index',
            ],
            [
                'parent_id' => 2,
                'order' => $order += 1,
                'title' => '视频管理',
                'icon' => 'fa-file-video-o',
                'uri' => 'videos.index',
            ]
        ]);
    }
}
