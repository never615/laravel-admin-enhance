<?php

namespace Mallto\Admin\Seeder\Menu;


use Illuminate\Database\Seeder;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Seeder\MenuSeederMaker;


class AdminUserGroupMenuSeeder extends Seeder
{
    use MenuSeederMaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $order = Menu::max('order');
        $parentId = 0;

        $menu = Menu::where("uri", "admin_manager")->first();

        if ($menu) {
            $order = $menu->order;
            $parentId = $menu->id;
        }

        $this->updateOrCreate(
            "admin_user_groups.index", $parentId, $order++, "管理账户分组", "fa-group");
    }
}
