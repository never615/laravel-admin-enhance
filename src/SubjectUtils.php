<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

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
                    throw new SubjectNotFoundException("主体未找到 getConfigByOwner");

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
     * 传入$subjectId参数可以优先直接使用缓存查询
     *
     * @param      $key
     * @param null $default
     * @param null $subject
     * @param null $subjectId
     *
     * @return null
     */
    public static function getConfigBySubjectOwner($key, $default = null, $subject = null, $subjectId = null)
    {
        if ( ! $subjectId) {
            if ($subject) {
                $subjectId = $subject->id;
            } else {
                try {
                    $subject = self::getSubject();
                    $subjectId = $subject->id;
                } catch (\Exception $exception) {
                    if (isset($default)) {
                        return $default;
                    } else {
                        throw $exception;
                        //\Log::warning($exception);
                        //\Log::warning(new \Exception());
                        //throw new SubjectNotFoundException("主体未找到 getConfigBySubjectOwner:" . $key);
                    }

                }
            }
        }

        $value = Cache::get('c_s_o_' . $subjectId . '_' . $key);
        if ( ! $value) {
            if ( ! $subject) {
                $subject = Subject::query()->findOrFail($subjectId);
            }

            $extraConfig = $subject->open_extra_config ?: [];

            $value = array_get($extraConfig, $key) ?: null;
            if ($value) {
                Cache::put('c_s_o_' . $subjectId . '_$key', $value, Carbon::now()->endOfDay());
            }
        }

        return $value ?? $default;
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
        $value = null;

        if ($subjectId) {
            $value = Cache::get('sub_dyna_conf_' . $key . '_' . $subjectId);
        }

        if ($value) {
            return $value;
        }

        if ( ! $subjectId) {
            try {
                $subjectId = self::getSubjectId();
            } catch (\Exception $exception) {
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
        Cache::put('sub_dyna_conf_' . $key . '_' . $subjectId, $value, 600);

        return $value;
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
        $value = null;

        if ($subject) {
            $value = Cache::get('sub_dyna_conf_' . $key . '_' . $subject->id);
        }

        if ($value) {
            return $value;
        }

        if ( ! $subject) {
            try {
                $subject = self::getSubject();
            } catch (\Exception $exception) {
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
        Cache::put('sub_dyna_conf_' . $key . '_' . $subject->id, $value, 600);

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
     * @return Subject|static
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
            $subject = Cache::get('sub_uuid' . $uuid);
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
                $subject = Cache::get('sub_admin_user_' . $user->id);
                if ( ! $subject) {
                    $subject = $user->subject;

                    Cache::put('sub_admin_user_' . $user->id, $subject, 300);
                }
            }
        } else {
            Cache::put('sub_uuid' . $subject->uuid, $subject, 600);
            Cache::put('sub_uuid' . $subject->extra_config[SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID],
                $subject, 600);
        }

        if ($subject) {
            return $subject;
        }

        throw new HttpException(422, "uuid参数错误:" . $uuid);
    }

}
