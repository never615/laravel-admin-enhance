<?php

namespace Mallto\Admin\Seeder\Menu;


use Mallto\Admin\Data\Menu;
use Illuminate\Database\Seeder;


class AdminUserGroupMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::where("uri", "admin_manager")->first();

        $order=10000;
        $parentId=0;
        if($menu){
            $order=$menu->order;
            $parentId=$menu->id;
        }

        Menu::updateOrCreate([
            'uri' => 'admin_user_groups.index',
        ], [
                'parent_id' => $parentId,
                'order'     => $order += 1,
                'title'     => '管理账户分组',
                'icon'      => 'fa-group',
            ]
        );

    }
}
