<?php
/**
 * Copyright (c) 2021. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
     * @param null $subject
     * @param null $default
     *
     * @return mixed|null
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

        $subjectId = $subject->id;

        $value = Cache::store('memory')->get('s_s' . $subjectId . '_' . $key);
        if ( ! isset($value) || is_null($value)) {
            $subjectSetting = SubjectSetting::query()
                ->select([ $key, 'public_configs', 'private_configs', 'subject_owner_configs' ])
                ->where('subject_id', $subject->id)
                ->first();

            $value = null;
            if ($subjectSetting) {
                $value = $subjectSetting->$key
                    ?? $subjectSetting['public_configs']
                    ?? $subjectSetting['private_configs']
                    ?? $subjectSetting['subject_owner_configs'];
            }

            if ( ! is_null($value)) {
                Cache::store('memory')
                    ->put('s_s' . $subjectId . '_' . $key, $value,
                        Carbon::now()->endOfDay());

                return $value;
            }
        } else {
            return $value;
        }

        if ( ! is_null($default)) {
            return $default;
        }

        throw new NotSettingBySubjectOwnerException($key . "未配置," . $subject->id);
    }


    /**
     * 获取当前主体可用的支付类型
     *
     * @param null  $subject
     * @param array $default
     *
     * @return array|mixed
     */
    public static function getSubjectAllowPayType($subject = null, $default = [])
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

        $subjectSetting = SubjectSetting::query()
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        $subjectAllowPayType = json_decode1($subjectSetting->allow_pay_type) ?? [];
        $allAllowPayType = SubjectSetting::ALLOW_PAY_TYPE;

        $payType = [];

        if (empty($subjectAllowPayType)) {
            return $default;
        }

        foreach ($subjectAllowPayType as $key => $value) {
            if (array_key_exists($value, $allAllowPayType)) {
                $payType[$value] = $allAllowPayType[$value];
            }
        }

        return $payType;
    }
}
