<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Mallto\Admin\Data\FrontMenu;
use Mallto\Tool\Exception\ResourceException;

/**
 * 生成权限的seeder基础方法
 *
 * Create by PhpStorm.
 * User: never615
 * Date: 24/04/2017
 * Time: 4:51 PM
 */
trait FrontMenuSeederMaker
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
            $tempMenu = FrontMenu::query()->where('uri', $uri)
                ->first();
            if ($tempMenu && $tempMenu->parent_id != $parentId) {
                $updateChildMenu = true;
            }
        }

        //处理多语言标题
        $titleParts = explode(',', $title);
        if (count($titleParts) > 3) {
            throw new ResourceException('标题包含三种以上语言.');
        }

        $updateData = [
            'parent_id' => $parentId,
            'title' => $titleParts[0],//默认中文
            'tc_title' => $titleParts[1] ?? null,
            'en_title' => $titleParts[2] ?? null,
            'icon' => $icon,
            "path" => $path,
//            "sub_title" => $subTitle,
        ];
        if (!is_null($order)) {
            $updateData = array_merge($updateData, [
                'order' => $order,
            ]);
        }

        $menu = FrontMenu::query()->updateOrCreate([
            'uri' => $uri,
        ], $updateData);

        if ($updateChildMenu) {
            FrontMenu::query()
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
        $parentMenu = FrontMenu::find($parentId);

        if ($parentMenu) {
            if (!empty($parentMenu->path)) {
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
