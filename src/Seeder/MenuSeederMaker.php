<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Mallto\Admin\Data\Menu;

/**
 * 生成权限的seeder基础方法
 *
 * Create by PhpStorm.
 * User: never615
 * Date: 24/04/2017
 * Time: 4:51 PM
 */
trait MenuSeederMaker
{

    /**
     * @param      $uri
     * @param      $parentId
     * @param      $order
     * @param      $title
     * @param      $icon
     * @param null $subTitle
     *
     * @return mixed
     */
    protected function updateOrCreate($uri, $parentId, $order, $title, $icon, $subTitle = null)
    {

        $path = $this->updatePath($parentId);

        $updateChildMenu = true;
        //如果修改了 parent_id,则修改所有子菜单的 path
        if ($parentId) {
            $tempMenu = Menu::query()->where('uri', $uri)
                ->first();
            if ($tempMenu && $tempMenu->parent_id != $parentId) {
                $updateChildMenu = true;
            }
        }

        $updateData = [
            'parent_id' => $parentId,
            'title'     => $title,
            'icon'      => $icon,
            "path"      => $path,
//            "sub_title" => $subTitle,
        ];
        if ( ! is_null($order)) {
            $updateData = array_merge($updateData, [
                'order' => $order,
            ]);
        }

        $menu = Menu::query()->updateOrCreate([
            'uri' => $uri,
        ], $updateData);

        if ($updateChildMenu) {
            Menu::query()
                ->where('path', 'like', '%.' . $menu->id . '.%')
                ->chunk(50, function ($menus) {
                    foreach ($menus as $menu) {
                        $path = $this->updatePath($menu->parent_id);

                        $menu->path = $path;
                        $menu->save();
                    }
                });
        }

        return $menu;
    }


    private function updatePath($parentId)
    {
        $parentMenu = Menu::find($parentId);

        if ($parentMenu) {
            if ( ! empty($parentMenu->path)) {
                $path = $parentMenu->path . $parentMenu->id . ".";
            } else {
                $path = "." . $parentMenu->id . ".";
            }
        } else {
            $path = null;
        }

        return $path;
    }
}
