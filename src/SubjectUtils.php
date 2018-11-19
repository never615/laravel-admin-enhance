<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
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
     * 获取只有项目拥有者才能编辑的配置项
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return null
     */
    public static function getConfigByOwner($key, $default = null, $subject = null)
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
     * 获取只有主体拥有者才能编辑的配置项
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return null
     */
    public static function getConfigBySubjectOwner($key, $default = null, $subject = null)
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

        $extraConfig = $subject->open_extra_config ?: [];

        return array_get($extraConfig, $key) ?: $default;
    }


    /**
     * 获取可以动态设置key的配置项
     *
     * 只有owner可以编辑
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return mixed|null
     */
    public static function getDynamicKeyConfigByOwner($key, $default = null, $subject = null)
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
     * 获取主体系统设置,只有mallto才可以编辑
     *
     * 对应对题管理第四个tab
     *
     * @deprecated use getConfigByOwner()
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return mixed
     */
    public static function getSubjectExtraConfig($key, $default = null, $subject = null)
    {
        return self::getConfigByOwner($key, $default, $subject);
    }


    /**
     * 获取主体开放编辑的配置项
     *
     * 对应主体设置第二个tab
     *
     * @deprecated use getConfigBySubjectOwner()
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return null
     */
    public static function getSubjectOpenExtraConfig($key, $default = null, $subject = null)
    {
        return self::getConfigBySubjectOwner($key, $default, $subject);
    }


    /**
     * 获取主体的系统参数配置
     *
     * 主要是第三方接口地址和签名配置
     *
     * 对应主体管理第五个tab
     *
     * @deprecated use getDynamicKeyConfigByOwner()
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @return mixed
     */
    public static function getSubectConfig2($key, $default = null, $subject = null)
    {
        return self::getDynamicKeyConfigByOwner($key, $default, $subject);
    }


    /**
     * 获取主体的配置信息
     *
     * @deprecated use getSubectConfig2
     *
     * @param      $subject
     * @param      $key
     * @param null $default
     * @return mixed
     */
    public static function getSubectConfig($subject, $key, $default = null)
    {
        return self::getDynamicKeyConfigByOwner($key, $default, $subject);
    }


}
