<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\Exception\SubjectConfigException;
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
     * 保存在 extra_config 中
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
        $subjectId = null;

        if ($subject) {
            if (is_numeric($subject)) {
                $subjectId = $subject;
            } else {
                $subjectId = $subject->id;
            }
        }

        if ($subjectId) {
            $value = Cache::store('local_redis')->get('c_s_ec_' . $subjectId . '_' . $key);
            if ( ! is_null($value)) {
                return $value;
            }
        } else {
            $subjectId = self::getSubjectId();
        }

        $subject = Subject::query()->findOrFail($subjectId);

        $extraConfig = $subject->extra_config ?: [];

        $value = array_get($extraConfig, $key);
        if ( ! is_null($value)) {

        } elseif ( ! is_null($default)) {
            $value = $default;
        } else {
            $value = '';
        }

        Cache::store('local_redis')->put('c_s_ec_' . $subjectId . '_' . $key, $value,
            Carbon::now()->endOfDay());

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
        $subjectId = null;
        if ($subject) {
            if (is_numeric($subject)) {
                $subjectId = $subject;
            } else {
                $subjectId = $subject->id;
            }
        }

        if ($subjectId) {
            $value = Cache::store('local_redis')->get('c_s_o_' . $subjectId . '_' . $key);
            if ( ! is_null($value)) {
                return $value;
            }
        } else {
            $subjectId = self::getSubjectId();

        }

        $subject = Subject::query()->findOrFail($subjectId);

        $extraConfig = $subject->open_extra_config ?: [];

        $value = array_get($extraConfig, $key);

        if ( ! is_null($value)) {

        } elseif ( ! is_null($default)) {
            $value = $default;
        } else {
            $value = '';
        }

        Cache::store('local_redis')->put('c_s_o_' . $subjectId . '_' . $key, $value,
            Carbon::now()->endOfDay());

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
     * @param                  $key
     * @param null             $subject
     * @param null             $default
     *
     * @return mixed|null
     */
    public static function getDynamicKeyConfigByOwner($key, $subject = null, $default = null)
    {
        $subjectId = null;

        if ($subject) {
            if (is_numeric($subject)) {
                $subjectId = $subject;
            } else {
                $subjectId = $subject->id;
            }
        }

        if ($subjectId) {
            $value = Cache::store('local_redis')->get('sub_dyna_conf_' . $key . '_' . $subjectId);
            if ( ! is_null($value)) {
                return $value;
            }
        } else {
            $subjectId = self::getSubjectId();
        }

        //缓存中没有查到，查数据库
        $subjectConfig = SubjectConfig::where("subject_id", $subjectId)
            ->where("key", $key)
            ->first();

        if ($subjectConfig) {
            $value = $subjectConfig->value;
        } elseif ( ! is_null($default)) {
            $value = $default;
        } else {
            throw new SubjectConfigException($key . "未配置," . $subjectId);
        }

        Cache::store('local_redis')->put('sub_dyna_conf_' . $key . '_' . $subjectId, $value,
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
        $subjectId = null;

        if ($subject) {
            if (is_numeric($subject)) {
                $subjectId = $subject;
            } else {
                $subjectId = $subject->id;
            }
        }

        if ($subjectId) {
            $value = Cache::store('local_redis')->get('sub_dyna_conf_' . $key . '_' . $subjectId);
            if ( ! is_null($value)) {
                return $value;
            }
        } else {
            $subjectId = self::getSubjectId();
        }

        //缓存中没有查到，查数据库
        $subjectConfig = SubjectConfig::where("subject_id", $subjectId)
            ->whereIn("type", [ 'public', 'front' ])
            ->where("key", $key)
            ->first();

        if ($subjectConfig) {
            $value = $subjectConfig->value;
        } elseif ( ! is_null($default)) {
            $value = $default;
        } else {
            throw new SubjectConfigException($key . "未配置," . $subjectId);
        }

        Cache::store('local_redis')->put('sub_dyna_conf_' . $key . '_' . $subjectId, $value,
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
                if (strlen($uuid) > 10) {
                    $uuid = null;
                }
            }
        } else {
            $uuid = \Request::header("UUID");
            if (is_null($uuid)) {
                $uuid = \Request::input("uuid");
                if (strlen($uuid) > 10) {
                    $uuid = null;
                }
            }
        }

        if (empty($uuid) && \Admin::user()) {
            $uuid = \Admin::user()->subject->uuid;
        }

        if (empty($uuid)
            && ! empty(config('auth.guards.admin_api'))
            && $adminUser = Auth::guard("admin_api")->user()) {
            $uuid = $adminUser->subject->uuid;
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
            $subject = Cache::store('local_redis')->get('sub_uuid' . $uuid);
            if ( ! $subject) {
                $subject = Subject::where("uuid", $uuid)->first();
                if ( ! $subject) {
                    $subject = Subject::where('extra_config->' . SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID,
                        $uuid)
                        ->first();
                }
            }

            if ( ! $subject) {
                throw new HttpException(422, "uuid参数错误:" . $uuid);
            }
        }

        if ( ! $subject) {
            //按照管理端请求的方式,尝试获取subject
            $user = \Admin::user();
            if ($user) {
                $subject = Cache::store('local_redis')->get('sub_admin_user_' . $user->id);
                if ( ! $subject) {
                    $subject = $user->subject;

                    Cache::store('local_redis')->put('sub_admin_user_' . $user->id, $subject, 300);
                }
            }
        } else {
            Cache::store('local_redis')->put('sub_uuid' . $subject->uuid, $subject,
                Carbon::now()->endOfDay());
            if ($subject->extra_config && isset($subject->extra_config[SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID])) {
                Cache::store('local_redis')->put('sub_uuid' . $subject->extra_config[SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID],
                    $subject, Carbon::now()->endOfDay());
            }
        }

        if ($subject) {
            return $subject;
        }

        throw new HttpException(422, "uuid参数错误:" . $uuid);
    }


    /**
     * 获取当前主体 通过第三方项目标识
     *
     * @param null $app
     *
     * @param null $uuid
     *
     * @return Subject
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public
    static function getSubjectByThirdProjectId(
        $thirdProjectId
    ) {
        $subject = Cache::store('local_redis')->get('sub_proj_id' . $thirdProjectId);
        if ( ! $subject) {
            $subject = Subject::where("third_part_mall_id", $thirdProjectId)->first();
            if ($subject) {
                Cache::store('local_redis')->put('sub_proj_id' . $thirdProjectId, $subject,
                    Carbon::now()->endOfDay());
            }
        }

        if ($subject) {
            return $subject;
        }

        throw new HttpException(422, "第三方项目标识找不到对应项目:" . $thirdProjectId);
    }

}
