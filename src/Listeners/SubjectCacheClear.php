<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Listeners\Events\SubjectSaved;
use Mallto\Admin\SubjectUtils;

/**
 * Subject 保存后清理相关缓存。
 *
 * 清理范围：
 *   1. 更新 sub_uuid 缓存（subject 对象缓存）
 *   2. 清理 getConfigBySubjectOwner 缓存（open_extra_config，前缀 so_ / c_s_o_）
 *   3. 清理 getConfigByOwner 缓存（extra_config，前缀 ec_ / c_s_ec_）
 *   以上清理都会通过 Redis Pub/Sub 广播到所有服务器，
 *   同时清理 swoole_table 和 local_redis。
 *
 * User: never615 <never615.com>
 * Date: 2020/5/12
 */
class SubjectCacheClear implements ShouldQueue
{
    public $queue = 'mid';

    public function handle(SubjectSaved $subjectSaved)
    {
        $subjectId = $subjectSaved->subjectId;

        try {
            $subject = Subject::query()->findOrFail($subjectId);
        } catch (\Throwable $e) {
            Log::error('[SubjectCacheClear] Subject 不存在: ' . $subjectId);
            return;
        }

        // 1. 更新 subject 对象缓存（本地 Redis，其他服务器靠 SubjectUtils::getSubject 覆盖写入）
        Cache::store('local_redis')->forever('sub_uuid_' . $subject->uuid, $subject);
        if (isset($subject->extra_config['uuid'])) {
            Cache::store('local_redis')->forever(
                'sub_uuid_' . $subject->extra_config['uuid'],
                $subject
            );
        }

        // 2. 清理 open_extra_config 缓存（getConfigBySubjectOwner）
        //    swoole_table key 前缀: so_{subjectId}_
        //    local_redis key 前缀: c_s_o_{subjectId}_
        SubjectUtils::clearConfigBySubjectOwner($subjectId);

        // 3. 清理 extra_config 缓存（getConfigByOwner）
        //    swoole_table key 前缀: ec_{subjectId}_
        //    local_redis key 前缀: c_s_ec_{subjectId}_
        SubjectUtils::clearConfigByOwner($subjectId);

        Log::info('[SubjectCacheClear] 缓存已清理并广播', ['subjectId' => $subjectId]);
    }
}
