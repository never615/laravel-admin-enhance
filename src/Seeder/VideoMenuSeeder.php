<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;


use Encore\Admin\Auth\Database\Menu;
use Illuminate\Database\Seeder;

class VideoMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = Menu::where("title", "视频管理")
            ->orWhere("title", "视频")->first();


        if ($menu) {
            return;
        }


        $menu = Menu::where("title", "报表管理")->first();

        Menu::create([
            'parent_id' => $menu->parent_id,
            'order'     => $menu->order + 1,
            'title'     => '视频管理',
            'icon'      => 'fa-file-video-o',
            'uri'       => 'videos.index',
        ]);
    }
}
