<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\SubjectSettingUtils;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\PermissionDeniedException;

/**
 * Class SubjectSettingController
 *
 * @package Mallto\Mall\Controller\Api
 */
class SubjectSettingController extends Controller
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
        $queryNames = explode(',', $queryName);

        $subject = SubjectUtils::getSubject();

        $subjectSetting = SubjectSetting::query()
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        $result = [];

        foreach ($queryNames as $queryName) {
            //是否在可请求的key中
            if ( ! in_array($queryName, $subjectSetting->front_column ?? [])
                && ! in_array($queryName, $subjectSetting->public_configs ?? [])) {
                throw new PermissionDeniedException('权限拒绝:' . $queryName);
            }

            $value = SubjectSettingUtils::getSubjectSetting($queryName, $subject);

            if (in_array($request->name, $subjectSetting->file_type_column ?? [])) {
                $value = config("app.file_url_prefix") . $value;
            }

            if (str_contains($value, 'image')) {
                $value = config("app.file_url_prefix") . $value;
            }
            $result[$queryName] = $value;
        }

        return $result;
    }

}
