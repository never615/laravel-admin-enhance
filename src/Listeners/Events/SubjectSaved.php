<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners\Events;

use Illuminate\Queue\SerializesModels;

/**
 * 主体创建成功事件
 *
 * User: never615 <never615.com>
 * Date: 2019/10/9
 * Time: 3:31PM
 */
class SubjectSaved
{

    use SerializesModels;

    public $subjectId;

    public $new;


    /**
     * @param      $subjectId
     * @param bool $new 是否是新创建
     */
    public function __construct($subjectId, $new = true)
    {
        $this->subjectId = $subjectId;
        $this->new = $new;
    }

}
