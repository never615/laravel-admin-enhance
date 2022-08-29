<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\Subject;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2019/3/28
 * Time: 7:17 PM
 */
class AdminUtils
{

    /**
     * 获取当前登录用户的信息
     *
     * @return array
     */
    public static function getLoginUserData()
    {
        $adminUser = session(CacheConstants::SESSION_ADMIN_USER);
        if ($adminUser) {
            if (is_array($adminUser)) {
                //兼容旧数据
                $adminUser = null;
            } else {
                $adminUser = unserialize($adminUser);
            }
        }
        $isOwner = session(CacheConstants::SESSION_IS_OWNER);
        $currentSubject = session(CacheConstants::SESSION_CURRENT_SUBJECT);
        if ($currentSubject) {
            if (is_array($currentSubject)) {
                //兼容旧数据
                $currentSubject = null;
            } else {
                $currentSubject = unserialize($currentSubject);
            }
        }

        if ($isOwner === null || ! $currentSubject || ! $adminUser) {
            $adminUser = Admin::user();

            if ($adminUser) {
                $isOwner = $adminUser->isOwner();
                $currentSubject = $adminUser->subject;

                session([
                    //CacheConstants::SESSION_ADMIN_USER      => array_except($adminUser->toArray(),
                    //    [ "roles", "subject" ]),
                    CacheConstants::SESSION_ADMIN_USER      => serialize($adminUser),
                    CacheConstants::SESSION_IS_OWNER        => ($adminUser->isOwner() ? 1 : 0),
                    CacheConstants::SESSION_CURRENT_SUBJECT => serialize($adminUser->subject),
                ]);
            }
        }

//        \Log::debug(session(CacheConstants::SESSION_ADMIN_USER));
//        \Log::debug(session(CacheConstants::SESSION_CURRENT_SUBJECT));

        return [ $adminUser, $isOwner, $currentSubject ];
    }


    /**
     * 判断当前登录用户是否是owner
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public static function isOwner()
    {
        $isOwner = session(CacheConstants::SESSION_IS_OWNER);

        if ($isOwner === null) {
            [ $adminUser, $isOwner, $currentSubject ] = self::getLoginUserData();
        }

        return $isOwner;
    }


    /**
     * 判断当前登录用户是否是总部
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public static function isBase()
    {
        [ $adminUser, $isOwner, $currentSubject ] = self::getLoginUserData();

        return $currentSubject->base;
    }


    /**
     * 获取当前管理端登录管理用户
     */
    public static function getCurrentAdminUser()
    {
        $currentAdminUser = session(CacheConstants::SESSION_ADMIN_USER);
        if ($currentAdminUser) {
            if (is_array($currentAdminUser)) {
                //兼容旧数据
                $currentAdminUser = null;
            } else {
                $currentAdminUser = unserialize($currentAdminUser);
            }
        }

        if ( ! $currentAdminUser) {
            [ $currentAdminUser, $isOwner, $currentSubject ] = self::getLoginUserData();
        }

//        \Log::debug(session(CacheConstants::SESSION_ADMIN_USER));
//        \Log::debug(\GuzzleHttp\json_encode($currentAdminUser));

        return $currentAdminUser;
    }


    /**
     * 获取当前管理端登录用户所属主体
     */
    public static function getCurrentSubject()
    {
        $currentSubject = session(CacheConstants::SESSION_CURRENT_SUBJECT);
        if ($currentSubject) {
            if (is_array($currentSubject)) {
                //兼容旧数据
                $currentSubject = null;
            } else {
                $currentSubject = unserialize($currentSubject);
            }
        }

        if ( ! $currentSubject) {
            [ $currentAdminUser, $isOwner, $currentSubject ] = self::getLoginUserData();
        }

        return $currentSubject;
    }


    /**
     * 获取当前管理端登录用户所属主体id
     */
    public static function getCurrentSubjectId()
    {
        return self::getCurrentSubject()->id ?? null;
    }


    public static function getCurrentAdminUserId()
    {
        return self::getCurrentAdminUser()->id ?? null;
    }


    /**
     * 根据主体id 查询 subject
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getSubject($id)
    {
        $subject = Cache::get("subject_" . $id);

        if ( ! $subject) {
            $subject = Subject::find($id);
            self::cacheSubject($subject);
        }

        return $subject;
    }


    /**
     * 是否是管理端请求
     *
     * @return bool
     */
    public static function isAdminRequest()
    {
        return starts_with(request()->getPathInfo(), "/admin");
    }


    /**
     * 缓存主体数据
     *
     * @param $subject
     *
     * @deprecated use CacheUtils
     */
    public static function cacheSubject($subject)
    {
        CacheUtils::putSubject($subject);
    }


    /**
     * 删除缓存的主体数据
     *
     * @param $id
     *
     * @deprecated use CacheUtils
     *
     */
    public static function forgetSubject($id)
    {
        CacheUtils::forgetSubject($id);
    }


    /**
     * clea menu cache
     *
     * @deprecated use CacheUtils
     */
    public static function clearMenuCache()
    {
        CacheUtils::clearMenuCache();
    }

}
