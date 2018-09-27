<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Exception\SubjectConfigException;
use Mallto\Admin\Exception\SubjectNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 工具类
 * Created by PhpStorm.
 * User: never615
 * Date: 05/11/2016
 * Time: 4:22 PM
 */
class SubjectUtils
{
    private static $subject;


    /**
     *
     * 获取主体系统设置
     * 如:是否需要完成自选标签才算注册完成
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return mixed
     */
    public static function getSubjectExtraConfig($key, $default = null, $subject = null)
    {
        if (!$subject) {
            try {
                $subject = self::getSubject();
            } catch (\Exception $exception) {
                if ($default) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到");

                }
            }
        }

        $extraConfig = $subject->extra_config ?: [];

        return array_get($extraConfig, $key) ?: $default;
    }


    /**
     * 获取主体的配置信息
     *
     * 主要是第三方接口地址和签名配置
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return mixed
     */
    public static function getSubectConfig2($key, $default = null, $subject = null)
    {
        if (!$subject) {
            try {
                $subject = self::getSubject();
            } catch (\Exception $exception) {
                if ($default) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到");

                }
            }
        }

        $subjectConfig = $subject->subjectConfigs()
            ->where("key", $key)
            ->first();
        if (!$subjectConfig) {
            if ($default) {
                return $default;
            } else {
                throw new SubjectConfigException($key."未配置");
            }
        }

        return $subjectConfig->value;
    }


    /**
     * 获取uuid
     *
     * @return mixed
     */
    public static function getUUID()
    {
        if (self::$subject) {
            return self::$subject->uuid;
        }

        $uuid = Request::header("UUID");
        if (is_null($uuid)) {
            $uuid = Input::get("uuid");
        }

        if (empty($uuid) && \Admin::user()) {
            $uuid = \Admin::user()->subject->uuid;
        }

        if (empty($uuid)) {
            throw new HttpException(422, "uuid参数错误");
        }

        return $uuid;
    }

    /**
     * 获取uuid
     *
     * @return mixed
     */
    public static function getUUIDNoException()
    {
        if (self::$subject) {
            return self::$subject->uuid;
        }

        $uuid = Request::header("UUID");
        if (is_null($uuid)) {
            $uuid = Input::get("uuid");
        }

        if (empty($uuid) && \Admin::user()) {
            $uuid = \Admin::user()->subject->uuid;
        }

        return $uuid;
    }

    /**
     * 获取主体id
     *
     * @return mixed
     */
    public static function getSubjectId()
    {

        if (self::$subject) {
            return self::$subject->id;
        }

        try {
            $uuid = self::getUUID();
        } catch (HttpException $e) {
            $uuid = null;
        }

        if (!is_null($uuid)) {
            $subject = Subject::where("uuid", $uuid)->first();
            if ($subject) {
                return $subject->id;
            }
        }

        $user = \Admin::user();
        if ($user) {
            $subject = $user->subject;
            if ($subject) {
                return $subject->id;
            }
        }

        throw new HttpException(422, "uuid参数错误".$uuid);
    }

    /**
     * 设置主体,测试用
     *
     * @param $subject
     */
    public static function setSubject($subject)
    {
        self::$subject = $subject;
    }


    /**
     * 获取主体
     *
     * @return Subject|null|static
     */
    public static function getSubject()
    {
        if (self::$subject) {
            return self::$subject;
        }

        try {
            $uuid = self::getUUID();
        } catch (HttpException $e) {
            $uuid = null;
        }

        if (!is_null($uuid)) {
            $subject = Subject::where("uuid", $uuid)->first();
            if ($subject) {
                return $subject;
            }
        }

        $user = \Admin::user();
        if ($user) {
            $subject = $user->subject;
            if ($subject) {
                return $subject;
            }
        }

        throw new HttpException(422, "uuid参数错误:".$uuid);
    }

    /**
     *
     * @param                    $tableName
     * @param                    $subjectId
     * @param Administrator|null $adminUser
     * @return array|bool
     */
    public static function dynamicSubjectIds($tableName, $subjectId, Administrator $adminUser = null)
    {
        if (Schema::hasColumn($tableName, 'subject_id')) {
            if (!empty($adminUserId)) {
                //如果设置了manager_subject_ids,则优先处理该值

                $managerSubjectIds = $adminUser->manager_subject_ids;

                if (!empty($managerSubjectIds)) {
                    $tempSubject = new Subject();
                    $tempSubjectIds = $managerSubjectIds;

                    foreach ($managerSubjectIds as $managerSubjectId) {
                        $tempSubjectIds = array_merge($tempSubjectIds,
                            $tempSubject->getChildrenSubject($managerSubjectId));
                    }
                    $tempSubjectIds = array_unique($tempSubjectIds);
                } else {
                    $currentSubject = $adminUser->subject;
                    $tempSubjectIds = $currentSubject->getChildrenSubject();
                }
            } else {
                //1.获取当前登录账户属于哪一个主体
                $currentSubject = Subject::find($subjectId);
                //2.获取当前主体的所有子主体
                $ids = $currentSubject->getChildrenSubject($currentSubject->id);
                $tempSubjectIds = $ids;
            }

            return $tempSubjectIds;
        } else {
            return false;
        }
    }


    /**
     * @deprecated use getSubectConfig2
     * 获取主体的配置信息
     *
     * @param      $subject
     * @param      $key
     * @param null $default
     * @return mixed
     */
    public static function getSubectConfig($subject, $key, $default = null)
    {
        if (!$subject) {
            throw new SubjectNotFoundException("主体未找到");
        }

        $subjectConfig = $subject->subjectConfigs()
            ->where("key", $key)
            ->first();
        if (!$subjectConfig) {
            if ($default) {
                return $default;
            } else {
                throw new SubjectConfigException($key."未配置");
            }
        }

        return $subjectConfig->value;
    }


}
