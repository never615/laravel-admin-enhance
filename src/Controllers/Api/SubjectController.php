<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Mallto\Admin\Data\Subject;

/**
 * User: never615 <never615.com>
 * Date: 2020/2/14
 * Time: 4:37 下午
 */
class SubjectController extends Controller
{

    /**
     * 获取主体列表
     *
     * @return mixed
     */
    public function index()
    {
        return Subject::whereNotNull('uuid')->select('name', 'uuid')->get();
    }

}
