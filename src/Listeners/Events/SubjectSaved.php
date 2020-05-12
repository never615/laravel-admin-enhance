<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Listeners\Events;

use Illuminate\Queue\SerializesModels;
use Mallto\Mall\Data\Subject;

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
    /**
     * @var Subject
     */
    public $subject;


    public function __construct(Subject $subject)
    {
        $this->subject = $subject;
    }

}
