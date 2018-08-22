<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder\Menu;


use Illuminate\Database\Seeder;
use Mallto\Admin\Seeder\MenuSeederMaker;

class AdminManagerMenuSeeder extends Seeder
{
    use MenuSeederMaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $order = 100;

        $adminManagerMenu = $this->updateOrCreate(
            "admin_manager", 0, $order++, "管理", "fa-tasks");

        $order = $adminManagerMenu->order;


        $this->updateOrCreate(
            "subjects.index", $adminManagerMenu->id,
            $order++, "主体", "fa-shopping-bag");

        $this->updateOrCreate(
            "admins.index", $adminManagerMenu->id,
            $order++, "账户", "fa-users");

        $this->updateOrCreate(
            "roles.index", $adminManagerMenu->id,
            $order++, "角色", "fa-user");

        $this->updateOrCreate(
            "permissions.index", $adminManagerMenu->id,
            $order++, "权限", "fa-user");

        $this->updateOrCreate(
            "menus.index", $adminManagerMenu->id,
            $order++, "菜单", "fa-bars");

        $this->updateOrCreate(
            "reports.index", $adminManagerMenu->id,
            $order++, "报表管理", "fa-table");

        $this->updateOrCreate(
            "logs.index", $adminManagerMenu->id,
            $order++, "操作日志", "fa-history");

        $this->updateOrCreate(
            "uploads.index", $adminManagerMenu->id,
            $order++, "文件管理", "fa-file");

        $this->updateOrCreate(
            "videos.index", $adminManagerMenu->id,
            $order++, "视频管理", "fa-file-video-o");

        $this->updateOrCreate(
            "import_settings.index", $adminManagerMenu->id,
            $order++, "导入配置", "fa-connectdevelop");


        $this->updateOrCreate(
            "import_records.index", $adminManagerMenu->id,
            $order++, "数据导入", "fa-upload");

    }
}