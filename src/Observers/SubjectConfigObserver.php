<?php

namespace Mallto\Admin\Observers;

use Illuminate\Support\Facades\Cache;
use Mallto\Admin\Data\SubjectConfig;

class SubjectConfigObserver
{

    /**
     * Handle the user "updated" event.
     *
     * @param SubjectConfig $subjectConfig
     *
     * @return void
     */
    public function updated(SubjectConfig $subjectConfig)
    {
        //处理刷新缓存
        Cache::store('local_redis')->put('sub_dyna_conf_' . $subjectConfig->key . '_' . $subjectConfig->subject_id,
            $subjectConfig->value, 3600);
    }

}
