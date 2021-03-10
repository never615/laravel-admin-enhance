<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use Illuminate\Http\Request;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\Exception\SubjectNotFoundException;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\NotSettingBySubjectOwnerException;
use Mallto\Tool\Exception\PermissionDeniedException;

/**
 * Class SubjectSettingController
 *
 * @package Mallto\Mall\Controller\Api
 */
class SubjectSettingController extends \Mallto\Admin\Controllers\Api\SubjectConfigController
{

    /**
     * @param Request $request
     *
     * @return mixed|string|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $queryName = $request->name;

        $subject = SubjectUtils::getSubject();

        $subjectSetting = SubjectSetting::query()
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        //是否在可请求的key中
        if ( ! in_array($queryName, $subjectSetting->front_column)) {
            throw new PermissionDeniedException('权限拒绝');
        }

        $value = self::getDynamicSubjectSetting($queryName, $subject);

        if (in_array($request->name, $subjectSetting->file_type_column)) {
            $value = config("app.file_url_prefix") . $value;
        }

        return [ $queryName => $value ];
    }


    /**
     * 获取相关主体的项目配置
     *
     * @param      $key
     * @param null $subject
     * @param null $default
     *
     * @return mixed|null
     */
    public static function getDynamicSubjectSetting($key, $subject = null, $default = null)
    {
        if ( ! $subject) {
            if (isset($default)) {
                return $default;
            } else {
                throw new SubjectNotFoundException("主体未找到 getDynamicSubjectSetting");
            }
        }

        $subjectSetting = SubjectSetting::query()
            ->where('subject_id', $subject->id)
            ->first();

        $value = $subjectSetting->$key ?? null;

        if (is_null($value)) {
            if ($default) {
                return $default;
            } else {
                throw new NotSettingBySubjectOwnerException($key . "未配置," . $subject->id);
            }
        }

        return $value;
    }

}
