<?php
/**
 * Copyright (c) 2021. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\Exception\NotSettingBySubjectOwnerException;
use Mallto\Admin\Exception\SubjectNotFoundException;

/**
 * User: never615 <never615.com>
 * Date: 2021/3/16
 * Time: 7:20 下午
 */
class SubjectSettingUtils
{

    /**
     * 获取相关主体的项目配置
     *
     * @param      $key
     * @param null $subject |$subjectId
     * @param null $default
     *
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getSubjectSetting($key, $subject = null, $default = null)
    {
        if ( ! $subject) {
            try {
                $subject = SubjectUtils::getSubject();
            } catch (\Exception $exception) {
                if (isset($default)) {
                    return $default;
                } else {
                    throw new SubjectNotFoundException("主体未找到 subject setting");

                }
            }
        }

        if (is_numeric($subject)) {
            $subjectId = $subject;
        } else {
            $subjectId = $subject->id;
        }

        $value = Cache::store('local_redis')->get(SubjectSetting::getCacheKey($subjectId) . $key);
        if (is_null($value)) {
            if (Schema::hasColumn('subject_settings', $key)) {
                $subjectSetting = SubjectSetting::query()
                    ->select([ $key ])
                    ->where('subject_id', $subjectId)
                    ->first();
            } else {
                $subjectSetting = SubjectSetting::query()
                    ->select([ 'public_configs', 'private_configs', 'subject_owner_configs' ])
                    ->where('subject_id', $subjectId)
                    ->first();
            }

            if ($subjectSetting) {
                if (Schema::hasColumn('subject_settings', $key)) {
                    $value = $subjectSetting->$key;
                } else {
                    $value = $subjectSetting['public_configs'][$key]
                        ?? $subjectSetting['private_configs'][$key]
                        ?? $subjectSetting['subject_owner_configs'][$key]
                        ?? null;
                }
            }

            if ( ! is_null($value)) {
                Cache::store('local_redis')->put(SubjectSetting::getCacheKey($subjectId) . $key, $value,
                    Carbon::now()->endOfDay());

            } else {
                if ( ! is_null($default)) {
                    Cache::store('local_redis')->put(SubjectSetting::getCacheKey($subjectId) . $key, $default,
                        Carbon::now()->endOfDay());
                    $value = $default;
                } else {
                    Cache::store('local_redis')->put(SubjectSetting::getCacheKey($subjectId) . $key, '',
                        Carbon::now()->endOfDay());
                    $value = '';
                }
            }
        }

        if ( ! is_null($value)) {
            return $value;
        }

        throw new NotSettingBySubjectOwnerException($key . "未配置," . $subjectId);
    }
}
