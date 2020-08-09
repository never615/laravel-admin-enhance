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

        $order = 3;

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
            "admin_user_groups.index", $adminManagerMenu->id,
            $order++, "主体管理账户分组", "fa-group");

        $order++;

        $order++;

        $this->updateOrCreate(
            "import_records.index", $adminManagerMenu->id,
            $order++, "数据导入", "fa-upload");

        $this->updateOrCreate(
            "reports.index", $adminManagerMenu->id,
            $order++, "报表管理", "fa-table");

        $this->updateOrCreate(
            "uploads.index", $adminManagerMenu->id,
            $order++, "文件管理", "fa-file");

        $this->updateOrCreate(
            "videos.index", $adminManagerMenu->id,
            $order++, "视频管理", "fa-file-video-o");


    }
}
