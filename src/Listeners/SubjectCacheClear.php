<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Listeners\Events\SubjectSaved;

/**
 * User: never615 <never615.com>
 * Date: 2020/5/12
 * Time: 7:06 下午
 */
class SubjectCacheClear implements ShouldQueue
{

    /**
     * 任务将被推送到的连接名称.
     *
     * @var string|null
     */
    public $queue = 'mid';


    public function handle(SubjectSaved $subjectSaved)
    {
        $subjectId = $subjectSaved->subjectId;

        $subject = Subject::query()->findOrFail($subjectId);

        //处理刷新缓存

        //1. 清理subject缓存
        Cache::store('local_redis')->put('sub_uuid' . $subject->uuid, $subject, Carbon::now()->endOfDay());
        if (isset($subject->extra_config['uuid'])) {
            Cache::store('local_redis')->put('sub_uuid' . $subject->extra_config['uuid'], $subject,
                Carbon::now()->endOfDay());
        }

        //2. 清理subject的open_extra_config缓存
        Artisan::call('tool:redis_del_prefix --prefix=c_s_o_' . $subjectId);

        //3. 清理extra_config
        Artisan::call('tool:redis_del_prefix --prefix=c_s_ec_' . $subjectId);
    }

}
