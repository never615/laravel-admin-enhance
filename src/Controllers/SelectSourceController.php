<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllerss;

use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\InvalidParamException;

class SelectSourceController extends Controller
{

    /**
     * 举例:
     * ajax: data_source_url("subject_id")
     *
     * ajax_load: data_source_url("ajax_load")
     *
     * ->ajaxLoad("adminable_type", data_source_url("ajax_load"))
     *
     * ->load(data_source_url("load"))
     *
     * @param         $key
     * @param Request $request
     *
     * @return mixed
     */
    public function dataSource($key, Request $request)
    {
        //ajax和ajaxload使用,用户输入的搜索内容
        $perPage = $request->get("per_page", 15);

        $q = $request->get('q');
        //todo 自动设置id现在无效了
        //自动设置默认值使用,当前条目的id
        $id = $request->get("id", null);
        //ajaxload使用,父节点的值
        $fatherValue = $request->get('father_value');

        if ($key == 'ajax_load') {
            $key = $fatherValue;
        }

        $adminUser = Admin::user();

        $subject = $this->getSubject($request);
        if ( ! $subject) {
            $subject = $adminUser->subject;
        }

        //查询子主体
        $childSubjectIds = $subject->getChildrenSubject();

        switch ($key) {
            case "subject":
            case "subject_id":
                if ( ! is_null($id)) {
                    $id = explode(",", $id);

                    return Subject::select(DB::raw("id,name as text"))->findOrFail($id);
                } else {

                    return Subject::select(DB::raw("id,name as text"))
                        ->whereIn('id', $childSubjectIds)
                        ->where('name', '~*', "$q")
                        ->paginate($perPage, [ 'id', 'text' ]);
                }
                break;
            case "load": //load 模式是直接加载全部数据,不过是远程加载
                //form多级联动需要的数据
                switch ($q) {
                    case 'suject':
                        return Subject::select(DB::raw("id,name as text"))
//                            ->where("id", $childSubjectIds)
                            ->paginate($perPage, [ 'id', 'text' ]);
                        break;
                    default:
                        throw new InvalidParamException();
                        break;
                }
                break;
            default:
                throw new InvalidParamException("select source 参数错误");
                break;
        }
    }


    private
    function getSubject(
        $request
    ) {
        if ($request->subject_uuid) {
            return Subject::where("uuid", $request->subject_uuid)->firstOrFail();
        }

        return SubjectUtils::getSubject();
    }

}
