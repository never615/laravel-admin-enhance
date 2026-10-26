<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Facades\AdminE;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Exception\InvalidParamException;
use Mallto\Tool\Exception\PermissionDeniedException;

class SelectSourceController extends Controller
{

    public $selectSourceClassObjes = [];


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

        //ajax条件下,$q是搜索值.load和loads()方法下$q是父节点的值,父节点就是联动的form的父form
        $q = $request->get('q');
        //todo 自动设置id现在无效了
        //自动设置默认值使用,当前条目的id
        $id = $request->get("id", null);


        if ($id && !is_array($id)) {
            try {
                $id = explode(',', $id);
            } catch (\Exception $e) {
                $id = null;
            }
        }

        //ajaxload使用,父节点的值
        $fatherValue = $request->get('father_value');

        if ($key === 'ajax_load') {
            $key = $fatherValue;
        }

        $isAdminApi = $request->path() === 'admin/api' || starts_with($request->path(), 'admin/api/');
        $adminUser = $isAdminApi
            ? Auth::guard('admin_api')->user()
            : (Admin::user() ?? Auth::guard('admin_api')->user());
        if (!$adminUser) {
            throw new PermissionDeniedException('Not authenticated');
        }

        $subject = $this->getSubject($request, $adminUser);
        if (!$subject) {
            $subject = $adminUser->subject;
        }

        //查询子主体
        if ($adminUser->isOwner()) {
            //todo 临时处理,如果以后项目上千的要优化
            $childSubjectIds = Subject::pluck('id')->toArray();
        } else {
            $childSubjectIds = $subject->getChildrenSubject();
        }

        //初始化其他库添加的select source
        $selectSourceClasses = AdminE::getSelectSourceClass();

        foreach ($selectSourceClasses as $selectSourceClass) {
            $this->selectSourceClassObjes[] = app($selectSourceClass);
        }

        //特别处理主体数据
        if ($key === 'subject' || $key === 'subject_id') {
            if (!is_null($id)) {
                $requestedIds = array_map('intval', (array)$id);
                $allowedIds = array_values(array_intersect($requestedIds, $childSubjectIds));
                if (count($allowedIds) !== count(array_unique($requestedIds))) {
                    throw new PermissionDeniedException('Subject access denied');
                }

                return Subject::select(DB::raw("id,name as text"))
                    ->whereIn('id', $allowedIds)
                    ->get();
            } else {
                return Subject::select(DB::raw("id,name as text"))
                    ->whereIn('id', $childSubjectIds)
                    ->where('name', '~*', "$q")
                    ->paginate($perPage, ['id', 'text']);
            }
        } elseif ($key === 'load') { //load 模式是直接加载全部数据,不过是远程加载
            //form多级联动需要的数据
            if ($q === 'subject') {
                return Subject::select(DB::raw("id,name as text"))
                    ->whereIn('id', $childSubjectIds)
                    ->get();
                //->paginate($perPage, [ 'id', 'text' ]);
            }

            $result = $this->addLoad($q, $perPage, $childSubjectIds, $fatherValue);
            if ($result) {
                return $result;
            }
            throw new InvalidParamException("select source 参数错误");
        }

        $result = $this->addDataSource($key, $id, $childSubjectIds, $q, $perPage, $adminUser, $fatherValue);

        if ($result) {
            return $result;
        }

        throw new InvalidParamException("select source params error, invalid key:" . $key);
//        throw new InvalidParamException("select source 参数错误2");
    }


    /**
     * ajax load 分页加载数据
     *
     * 方便下级依赖库添加数据源
     *
     * @param $key
     * @param $id
     * @param $childSubjectIds
     * @param $q
     * @param $perPage
     * @param $adminUser
     * @param $fatherValue
     */
    public function addDataSource($key, $id, $childSubjectIds, $q, $perPage, $adminUser, $fatherValue)
    {

        foreach ($this->selectSourceClassObjes as $selectSourceClassObj) {
            $result = $selectSourceClassObj->addDataSource($key, $id, $childSubjectIds, $q, $perPage,
                $adminUser,
                $fatherValue);
            if ($result) {
                return $result;
            }
        }
    }


    /**
     * load 模式是直接加载全部数据,不过是远程加载
     *
     * 方便下级依赖库添加数据源
     *
     * @param $q
     * @param $perPage
     * @param $childSubjectIds
     * @param $fatherValue
     */
    public function addLoad($q, $perPage, $childSubjectIds, $fatherValue)
    {
        foreach ($this->selectSourceClassObjes as $selectSourceClassObj) {
            $result = $selectSourceClassObj->addLoad($q, $perPage, $childSubjectIds, $fatherValue);
            if ($result) {
                return $result;
            }
        }
    }


    private
    function getSubject($request, $adminUser)
    {
        if ($request->subject_uuid) {
            $subject = Subject::where("uuid", $request->subject_uuid)->firstOrFail();
            if (!$adminUser->isOwner() && !$this->canManageSubject($adminUser, $subject->id)) {
                throw new PermissionDeniedException('Subject access denied');
            }

            return $subject;
        }

        return $adminUser->subject ?: SubjectUtils::getSubject();
    }

    private function canManageSubject($adminUser, int $subjectId): bool
    {
        $managedRootIds = array_values(array_filter(array_map(
            'intval',
            (array)$adminUser->manager_subject_ids
        )));
        if (!$managedRootIds) {
            $managedRootIds = [(int)$adminUser->subject_id];
        }

        return Subject::query()
            ->where(function ($query) use ($managedRootIds) {
                foreach ($managedRootIds as $index => $rootId) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}(function ($nested) use ($rootId) {
                        $nested->where('id', $rootId)
                            ->orWhere('path', 'like', "%.{$rootId}.%");
                    });
                }
            })
            ->whereKey($subjectId)
            ->exists();
    }

}
