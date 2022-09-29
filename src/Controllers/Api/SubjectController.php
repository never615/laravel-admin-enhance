<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Mall\SubjectConfigConstants;

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
    public function index(Request $request)
    {
        $projectType = $request->get('project_type');

        $query = \Mallto\Mall\Data\Subject::query()
            ->orderBy('created_at', 'desc')
            ->whereNotNull('uuid')
            ->select('name', 'uuid');

        if ($projectType) {
            $query->where('extra_config->' . SubjectConfigConstants::OWNER_CONFIG_PROJECT_TYPE, $projectType);
        }

        return $query->get();
    }

}
