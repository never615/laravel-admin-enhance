<?php

namespace Mallto\Admin\Observers;

use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\Subject;

class SubjectObserver
{

    /**
     * Handle the user "updated" event.
     *
     * @param Subject $subject
     *
     * @return void
     */
    public function updated(Subject $subject)
    {
        //处理刷新缓存
        Cache::store('memory')->put('sub_uuid' . $subject->uuid, $subject, 600);
        if (isset($subject->extra_config['uuid'])) {
            Cache::store('memory')->put('sub_uuid' . $subject->extra_config['uuid'], $subject, 600);
        }
    }

}
