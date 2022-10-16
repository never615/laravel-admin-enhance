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
        $path = "";
        $parentMenu = Menu::find($parentId);
        if ($parentMenu) {
            if ( ! empty($parentMenu->path)) {
                $path = $parentMenu->path . $parentMenu->id . ".";
            } else {
                $path = "." . $parentMenu->id . ".";
            }
        }

        $updateData = [
            'parent_id' => $parentId,
            'title'     => $title,
            'icon'      => $icon,
            "path"      => $path,
            "sub_title" => $subTitle,
        ];
        if ( ! is_null($order)) {
            $updateData = array_merge($updateData, [
                'order' => $order,
            ]);
        }

        return Menu::updateOrCreate([
            'uri' => $uri,
        ], $updateData
        );
    }
}
