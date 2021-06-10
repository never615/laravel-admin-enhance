<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Exception\SubjectConfigException;
use Mallto\Admin\Exception\SubjectNotFoundException;
use Mallto\Tool\Exception\HttpException;

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
     * @param string      $key 参见 SubjectConfigConstants::class
     * @param null        $default
     * @param Subject|int $subject
     *
     * @return null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getConfigByOwner($key, $subject = null, $default = null)
    {
        if ($subject && is_numeric($subject)) {
            $value = Cache::store('memory')->get('c_s_ec_' . $subject . '_' . $key);
            if ($value) {
                return $value;
            } else {
                $subject = Subject::query()->findOrFail($subject);
            }
        }

        if ( ! $subject) {
            try {
                $subject = self::getSubject();
            } catch (Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到 getConfigByOwner");

                }
            }
        }
        $subjectId = $subject->id;
        $value = Cache::store('memory')->get('c_s_ec_' . $subjectId . '_' . $key);
        if ( ! $value) {
            $extraConfig = $subject->extra_config ?: [];

            $value = array_get($extraConfig, $key);
            $value = is_null($value) ? null : $value;
            if ($value) {
                Cache::store('memory')->put('c_s_ec_' . $subjectId . '_' . $key, $value,
                    Carbon::now()->endOfDay());
            }
        }

        $value = $value ?? $default;
        //if (is_null($value) || empty($value)) {
        //    \Log::warning("getConfigByOwner 有参数未配置:" . $key);
        //}

        return $value;
    }


    /**
     * 获取只有主体拥有者才能编辑的配置项:能传subject参数优先传subject
     *
     * open_extra_config 字段中保存的数据
     *
     * 对应主体管理的系统配置(owner)tab
     *
     * 传入$subjectId参数可以优先直接使用缓存查询
     *
     * @param             $key
     * @param Subject|int $subject subject or subject_id
     * @param null        $default
     *
     * @return null
     * @throws Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public static function getConfigBySubjectOwner($key, $subject = null, $default = null)
    {
        if ($subject && is_numeric($subject)) {
            $value = Cache::store('memory')->get('c_s_o_' . $subject . '_' . $key);
            if ($value) {
                return $value;
            } else {
                $subject = Subject::query()->findOrFail($subject);
            }
        } else {
            try {
                $subject = self::getSubject();
                $subjectId = $subject->id;
            } catch (Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw $exception;
                }
            }
        }

        $subjectId = $subject->id;

        $value = Cache::store('memory')->get('c_s_o_' . $subjectId . '_' . $key);
        if ( ! $value) {
            $extraConfig = $subject->open_extra_config ?: [];

            $value = array_get($extraConfig, $key);
            $value = is_null($value) ? null : $value;
            if ($value) {
                Cache::store('memory')->put('c_s_o_' . $subjectId . '_' . $key, $value,
                    Carbon::now()->endOfDay());
            }
        }

        $value = $value ?? $default;
        //if (is_null($value)) {
        //    \Log::warning("getConfigBySubjectOwner 有参数未配置:" . $key);
        //}

        return $value;
    }


    /**
     * 获取可以动态设置key的配置项
     *
     * 只有owner可以编辑
     *
     * 对应主体管理的最后一个tab,即:系统参数(owner)
     *
     * 对应subject_configs表
     *
     * @param      $key
     * @param null $subjectId
     * @param null $default
     *
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */

    public static function getDynamicKeyConfigByOwner($key, $subjectId = null, $default = null)
    {
        $value = null;

        if ($subjectId) {
            $value = Cache::store('memory')->get('sub_dyna_conf_' . $key . '_' . $subjectId);
        }

        if ($value) {
            return $value;
        }

        if ( ! $subjectId) {
            try {
                $subjectId = self::getSubjectId();
            } catch (Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到 getDynamicKeyConfigByOwner");
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

        $value = $subjectConfig->value ?? $default;
        Cache::store('memory')->put('sub_dyna_conf_' . $key . '_' . $subjectId, $value,
            Carbon::now()->endOfDay());

        return $value;
    }


    /**
     * 获取可以动态设置key的配置项,保存在subject_configs表中
     *
     * 公开配置,包含public和front
     *
     * 只有owner可以编辑
     *
     * 对应主体管理的最后一个tab,即:系统参数(owner)
     *
     * @param             $key
     * @param null        $default
     * @param Subject|int $subject
     *
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */

    public static function getDynamicPublicKeyConfigByOwner($key, $subject = null, $default = null)
    {
        if ($subject && is_numeric($subject)) {
            $subject = Subject::query()->findOrFail($subject);
        }

        $value = null;

        if ($subject) {
            $value = Cache::store('memory')->get('sub_dyna_conf_' . $key . '_' . $subject->id);
        }

        if ($value) {
            return $value;
        }

        if ( ! $subject) {
            try {
                $subject = self::getSubject();
            } catch (Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到 getDynamicPublicKeyConfigByOwner");

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

        $value = $subjectConfig->value;
        Cache::store('memory')->put('sub_dyna_conf_' . $key . '_' . $subject->id, $value,
            Carbon::now()->endOfDay());

        return $value;
    }


    /**
     * 获取uuid
     *
     * @param null $app
     *
     * @return mixed
     */
    public
    static function getUUID(
        $app = null
    ) {
        if ($app) {
            $uuid = $app['request']->header("UUID");
            if (is_null($uuid)) {
                $uuid = $app['request']->get("uuid");
            }
        } else {
            $uuid = \Request::header("UUID");
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
    public
    static function getUUIDNoException()
    {
        $uuid = \Request::header("UUID");
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
    public
    static function getSubjectId()
    {
        return self::getCacheSubject()->id;
    }


    /**
     * 获取缓存的subject,如有
     *
     * 该subject不具备orm功能
     *
     * @param null $uuid
     *
     * @return mixed
     * @deprecated
     *
     */
    public
    static function getCacheSubject(
        $uuid = null
    ) {
        return self::getSubject(null, $uuid);

    }


    /**
     * 获取当前主体
     *
     * @param null $app
     *
     * @param null $uuid
     *
     * @return Subject
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public
    static function getSubject(
        $app = null,
        $uuid = null
    ) {

        $subject = null;

        //按照接口请求的方式,尝试获取subject
        if ( ! $uuid) {
            try {
                $uuid = self::getUUID($app);
            } catch (HttpException $e) {
                $uuid = null;
            }
        }

        if ( ! is_null($uuid)) {
            $subject = Cache::store('memory')->get('sub_uuid' . $uuid);
            if ( ! $subject) {
                $subject = Subject::where("uuid", $uuid)->first();
                if ( ! $subject) {
                    $subject = Subject::where('extra_config->' . SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID,
                        $uuid)
                        ->first();
                }
            }
        }

        if ( ! $subject) {
            //按照管理端请求的方式,尝试获取subject

            $user = \Admin::user();
            if ($user) {
                $subject = Cache::store('memory')->get('sub_admin_user_' . $user->id);
                if ( ! $subject) {
                    $subject = $user->subject;

                    Cache::store('memory')->put('sub_admin_user_' . $user->id, $subject, 300);
                }
            }
        } else {
            Cache::store('memory')->put('sub_uuid' . $subject->uuid, $subject, Carbon::now()->endOfDay());
            if ($subject->extra_config && isset($subject->extra_config[SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID])) {
                Cache::store('memory')->put('sub_uuid' . $subject->extra_config[SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID],
                    $subject, Carbon::now()->endOfDay());
            }
        }

        if ($subject) {
            return $subject;
        }

        throw new HttpException(422, "uuid参数错误:" . $uuid);
    }

}
