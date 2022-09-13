<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\SubjectSetting;
use Mallto\Admin\Exception\SubjectConfigException;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\ResourceException;

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

        $requestQueryName = $request->name;

        $queryNames = explode(',', $requestQueryName);

        $subject = SubjectUtils::getSubject();

        $cacheKey = SubjectSetting::getCacheKey($subject->id);
        $result = Cache::store('local_redis')->get($cacheKey . $requestQueryName);

        if ($result) {
            return $result;
        }

        $subjectSetting = SubjectSetting::query()
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        $result = [];

        foreach ($queryNames as $queryName) {
            //是否在可请求的key中
            if (in_array($queryName, $subjectSetting->front_column ?? [])) {
                $value = $subjectSetting->$queryName;
            } else {
                $value = $subjectSetting->public_configs[$queryName] ?? null;
                if (is_null($value)) {
                    try {
                        $value = SubjectUtils::getDynamicKeyConfigByOwner($queryName, $subject, 'default');
                        if ($value === 'default') {
                            return null;
                        }
                    } catch (SubjectConfigException $subjectConfigException) {
                        return null;
                    }
                }
            }

            if ( ! $value) {
                throw new ResourceException($queryName . '不存在或权限拒绝');
            }

            if (in_array($queryName, $subjectSetting->file_type_column ?? [])) {
                $value = config("app.file_url_prefix") . $value;
            }

            //if (str_contains($value, 'image')) {
            //    $value = config("app.file_url_prefix") . $value;
            //}
            $result[$queryName] = $value;
        }

        Cache::store('local_redis')->put($cacheKey . $requestQueryName, $result, Carbon::now()->addMinutes(10));

        return $result;
    }

}
