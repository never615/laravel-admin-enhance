<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\SubjectConfigConstants;
use Mallto\Admin\SubjectUtils;

/**
 * User: never615 <never615.com>
 * Date: 2020/2/14
 * Time: 4:37 下午
 */
class SubjectFrontConfigController extends Controller
{

    /**
     * 根据uuid获取主体详情
     *
     * @param $uuid
     *
     * @return array
     */
    public function config()
    {
        $subject = SubjectUtils::getSubject();

        $frontConfigs = SubjectConfig::where(
            [
                'subject_id' => $subject->id,
                'type'       => 'front',
            ]
        )->pluck('value', 'key')->toArray();

        if (config('app.env') === 'integration') {
            $cdnBackendDomain = config('app.url');
        } else {
            $cdnBackendDomain = SubjectUtils::getDynamicKeyConfigByOwner('cdn_backend_domain',
                $subject->id,
                config('other.cdn_url') ?? config('app.url'));
        }

        return [
            'name'               => $subject->name,
            'wechat_uuid'        => $subject->wechat_uuid ?? $subject->uuid,
            'tenant_wechat_uuid' => SubjectUtils::getConfigByOwner(
                SubjectConfigConstants::OWNER_CONFIG_ADMIN_WECHAT_UUID, $subject),

            'front_configs' => array_merge($frontConfigs, [
                'cdn_backend_domain' => $cdnBackendDomain,
            ]),
        ];

    }

}
