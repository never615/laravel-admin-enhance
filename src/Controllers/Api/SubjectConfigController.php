<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mallto\Admin\SubjectUtils;

/**
 *
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/11/5
 * Time: 下午3:35
 */
class SubjectConfigController extends Controller
{

    /**
     * 根据key请求动态配置
     * @param Request $request
     *
     * @return mixed|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            "name" => "required",
        ]);

        $queryName = $request->name;

        $subject = SubjectUtils::getSubject();

        //判断参数是否是主体动态配置中的公共请求参数
        return SubjectUtils::getDynamicPublicKeyConfigByOwner($queryName, $subject);
    }

}
