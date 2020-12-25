<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 获取最近的主体
 *
 * User: never615 <never615.com>
 * Date: 2020/12/25
 * Time: 4:37 下午
 */
class NearestSubjectController extends Controller
{

    /**
     * 获取主体列表
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'longitude' => 'required',
            'latitude'  => 'required',
        ]);

        $longitude = $request->get('longitude');
        $latitude = $request->get('latitude');

        //获取最近的主体
        $result = DB::select("select name,uuid,
ST_DistanceSphere(ST_GeomFromText('point(' || longitude || ' ' || latitude || ')'),ST_GeometryFromText('POINT($longitude $latitude)')) distance
 from subjects where longitude is not null and extra_config->>'project_type' = 'map' order by distance limit 1 ");

        return $result;
    }

}
