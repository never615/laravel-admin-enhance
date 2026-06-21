<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\SubjectConfigConstants;

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
        $isMiniProgram = $this->booleanFilterValue($request, SubjectConfigConstants::OWNER_CONFIG_IS_MINI_PROGRAM);

        $query = Subject::query()
            ->orderBy('weight', 'desc')
            ->orderBy('created_at', 'desc')
            ->whereNotNull('uuid')
            ->select('name', 'uuid', 'weight');

        if ($projectType) {
            $query->where('extra_config->' . SubjectConfigConstants::OWNER_CONFIG_PROJECT_TYPE, $projectType);
        }

        if ($isMiniProgram !== null) {
            $query->whereRaw(
                "COALESCE(extra_config->>'" . SubjectConfigConstants::OWNER_CONFIG_IS_MINI_PROGRAM . "', '0') in (?, ?)",
                $isMiniProgram ? ['1', 'true'] : ['0', 'false']
            );
        }

        return $query->get();
    }


    protected function booleanFilterValue(Request $request, string $key): ?bool
    {
        if (!$request->exists($key)) {
            return null;
        }

        return filter_var($request->get($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

}
