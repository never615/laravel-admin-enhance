<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
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

    /**
     * 获取只有项目拥有者才能编辑的配置项
     *
     * 对应主体管理的"配置项"tab
     *
     *
     * @param string $key 参见 SubjectConfigConstants::class
     * @param null   $default
     * @param null   $subject
     *
     * @return null
     */
    public static function getConfigByOwner($key, $subject = null, $default = null)
    {
        if ( ! $subject) {
            try {
                $subject = self::getSubject();
            } catch (\Exception $exception) {
                if (isset($default)) {
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
     * 对应主体管理的系统配置(owner)tab
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     *
     * @return null
     */
    public static function getConfigBySubjectOwner($key, $default = null, $subject = null)
    {
        if ( ! $subject) {
            try {
                $subject = self::getSubject();
            } catch (\Exception $exception) {
                if (isset($default)) {
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
     * 对应主体管理的最后一个tab,即:系统参数(owner)
     *
     * @param      $key
     * @param null $subjectId
     * @param null $default
     *
     * @return mixed|null
     */
    public static function getDynamicKeyConfigByOwner($key, $subjectId = null, $default = null)
    {
        if ( ! $subjectId) {
            try {
                $subjectId = self::getSubjectId();
            } catch (\Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到");

                }
            }
        }

        $subjectConfig = SubjectConfig::where("subject_id", $subjectId)
            ->where("key", $key)
            ->first();

        if ( ! $subjectConfig) {
            if ($default) {
                return $default;
            } else {
                throw new SubjectConfigException($key . "未配置," . $subjectId);
            }
        }

        return $subjectConfig->value ?? $default;
    }


    /**
     * 获取可以动态设置key的配置项
     *
     * 公开配置,包含public和front
     *
     * 只有owner可以编辑
     *
     * 对应主体管理的最后一个tab,即:系统参数(owner)
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     *
     * @return mixed|null
     */
    public static function getDynamicPublicKeyConfigByOwner($key, $subject = null, $default = null)
    {
        if ( ! $subject) {
            try {
                $subject = self::getSubject();
            } catch (\Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到");

                }
            }
        }

        $subjectConfig = $subject->subjectConfigs()
            ->where("key", $key)
            ->whereIn("type", [ 'public', 'front' ])
            ->first();

        if ( ! $subjectConfig) {
            if ($default) {
                return $default;
            } else {
                throw new SubjectConfigException($key . "未配置," . $subject->id);
            }
        }

        return $subjectConfig->value;
    }


    /**
     * 获取uuid
     *
     * @param null $app
     *
     * @return mixed
     */
    public static function getUUID($app = null)
    {
        if ($app) {
            $uuid = $app['request']->header("UUID");
            if (is_null($uuid)) {
                $uuid = $app['request']->get("uuid");
            }
        } else {
            $uuid = Request::header("UUID");
            if (is_null($uuid)) {
                $uuid = \Request::input("uuid");
            }
        }

        if (empty($uuid) && \Admin::user()) {
            $uuid = \Admin::user()->subject->uuid;
        }

        if (empty($uuid)) {
            throw new HttpException(422, "uuid为空");
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
        $uuid = Request::header("UUID");
        if (is_null($uuid)) {
            $uuid = \Request::input("uuid");
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
        return self::getSubject()->id;
    }


    /**
     * 获取当前主体
     *
     * @param null $app
     *
     * @return Subject|static
     */
    public static function getSubject($app = null)
    {
        //按照接口请求的方式,尝试获取subject
        try {
            $uuid = self::getUUID($app);
        } catch (HttpException $e) {
            $uuid = null;
        }

        if ( ! is_null($uuid)) {
            $subject = Subject::where("uuid", $uuid)->first();
            if ($subject) {
                return $subject;
            } else {
                $subject = Subject::where('extra_config->' . SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID,
                    $uuid)
                    ->first();
                if ($subject) {
                    return $subject;
                }
            }
        }

        //按照管理端请求的方式,尝试获取subject
        $user = \Admin::user();
        if ($user) {
            $subject = $user->subject;
            if ($subject) {
                return $subject;
            }
        }

        throw new HttpException(422, "uuid参数错误:" . $uuid);
    }

}
