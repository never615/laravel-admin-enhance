<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2019/1/24
 * Time: 7:39 PM
 */
class CacheConstants
{

    /**
     * session 中缓存用户数据
     */
    const SESSION_ADMIN_USER = 'admin_user';
    /**
     * session中缓存是否是项目拥有者
     */
    const SESSION_IS_OWNER = 'is_owner';
    /**
     * session中缓存当前登录用户所属主体
     */
    const SESSION_CURRENT_SUBJECT = "current_subject";

//    /**
//     * session中缓存当前登录用户的id
//     */
//    const SESSION_CURRENT_ADMIN_USER_ID = "current_admin_user_id";
//
//    /**
//     *  session中缓存当前登录用户所属主体的id
//     */
//    const SESSION_CURRENT_SUBJECT_ID = "current_subject_id";

    /**
     * 缓存中保存 所有菜单缓存的对应key的值
     */
    const CACHE_MENU_KEYS = "cache_menu_keys";
}
