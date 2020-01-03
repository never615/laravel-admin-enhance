<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\Facades\Cache;

/**
 * User: never615 <never615.com>
 * Date: 2019/9/2
 * Time: 4:48 PM
 */
class CacheUtils
{

    /**
     * 缓存主体数据
     *
     * @param $subject
     */
    public static function putSubject($subject)
    {
        if ($subject) {
            Cache::put("subject_" . $subject->id, $subject, 60 * 60 * 24);
        }
    }


    /**
     * 删除缓存的主体数据
     *
     * @param $id
     */
    public static function forgetSubject($id)
    {
        Cache::forget("subject_" . $id);
    }


    /**
     * clea menu cache
     */
    public static function clearMenuCache()
    {
        $cacheMenuKeys = Cache::get(CacheConstants::CACHE_MENU_KEYS, []);

        foreach ($cacheMenuKeys as $cacheMenuKey) {
            Cache::forget($cacheMenuKey);
        }
    }


    /**
     * 缓存账号对应的菜单
     *
     * @param $cacheMenuKey
     * @param $result
     */
    public static function putMenu($cacheMenuKey, $result)
    {
        Cache::put($cacheMenuKey, $result, 30 * 60);
    }


    /**
     * 缓存保存菜单的key
     *
     * @param $cacheMenuKeys
     */
    public static function putMenuKeys($cacheMenuKeys)
    {
        Cache::put(CacheConstants::CACHE_MENU_KEYS, $cacheMenuKeys, 60 * 60 * 24);
    }


    /**
     * 缓存快捷访问菜单
     *
     * @param $adminUser
     * @param $speedy
     */
    public static function putSeedy($adminUser, $speedy)
    {
        Cache::put("speedy_" . $adminUser->id, $speedy, 30 * 60);
    }

}
