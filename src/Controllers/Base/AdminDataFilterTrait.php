<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\SubjectUtils;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * User: never615 <never615.com>
 * Date: 2019-09-12
 * Time: 17:01
 */
trait AdminDataFilterTrait
{

    /**
     * 列表也数据过滤权限检查
     *
     * @param $grid
     */
    protected function indexFilter($grid)
    {
        $this->indexFilterDataBySubject($grid);
    }


    /**
     * 创建页面权限检查
     */
    protected function createFilter()
    {

    }


    /**
     * 处理进入详情页/更新数据/删除数据的权限
     *
     * @param $id
     */
    protected function formFilterById($id)
    {
        $this->formFilterDataForSubjectById($id);
    }


    /**
     * 保存数据检查
     */
    protected function storeFilter()
    {

    }

    //=========== 以上几个方法涵盖了增删改查各个纬度,一般重写以上方法即可  =================


    /**
     * 编辑页权限检查
     *
     * @param $id
     */
    protected function editFilter($id)
    {
        $this->formFilterById($id);
    }


    /**
     * 更新数据
     *
     * @param $id
     */
    protected function updateFilter($id)
    {
        $this->formFilterById($id);
    }


    /**
     * 删除数据动作权限检查
     *
     * @param $id
     */
    protected function destroyFilter($id)
    {
        $this->formFilterById($id);
    }


    /**
     * 过滤grid显示下的数据
     *
     * 处理subject纬度的数据
     *
     * 支持subject父子关系查看数据
     *
     * @param $grid
     */
    protected function indexFilterDataBySubject($grid)
    {
        if ( ! $this->adminUser) {
            $adminUser = Admin::user();
            $this->adminUser = $adminUser;
        } else {
            $adminUser = $this->adminUser;
        }

        if ( ! $adminUser->isOwner()) {
            //过滤数据展示使用

            if ($this->dataViewMode == 'all') {
                //根据账号所属的总公司,显示其下全部主体的数据
                $subject = $adminUser->subject;
                $baseSubject = $subject->baseSubject();

                if ($baseSubject && $baseSubject->base) {
                    $tempSubjectIds = $baseSubject->getChildrenSubject();
                } else {
                    throw new HttpException(422, "没有父级总公司主体,无法查看,请检查设置");
                }
            } else {
                if (method_exists($this->getModel(), "scopeDynamicData")) {
                    //获取登录账号的所有子主体
                    $currentSubject = $adminUser->subject;
                    $tempSubjectIds = $currentSubject->getChildrenSubject();

                    //如果设置了 manager_subject_ids,则需要和并处理如果设置了manager_subject_ids数据
                    $managerSubjectIds = $adminUser->manager_subject_ids;
                    if ( ! empty($managerSubjectIds)) {
                        $tempSubject = new Subject();
                        $tempManagerSubjectIds = $managerSubjectIds;

                        foreach ($managerSubjectIds as $managerSubjectId) {
                            $tempManagerSubjectIds = array_merge($tempManagerSubjectIds,
                                $tempSubject->getChildrenSubject($managerSubjectId));
                        }
                        $tempSubjectIds = array_unique(array_merge($tempSubjectIds, $tempManagerSubjectIds));
                    }

                } else {
                    throw new HttpException(500, "系统错误,未配置scopeDynamicData");
                }
            }

            if ($this->tableName == "subjects") {
                $grid->model()->whereIn("id", $tempSubjectIds);
            } elseif (Schema::hasColumn($this->tableName, "subject_id")) {
                $grid->model()->whereIn($this->getTableName() . ".subject_id", $tempSubjectIds);
            }
        }
    }


    /**
     * 处理subject纬度阻止没有数据权限的操作
     *
     * @param $id
     */
    protected function formFilterDataForSubjectById($id)
    {
        $model = resolve($this->getModel());
        //检查记录是否已经删除
        $obj = $model::find($id);
        if ( ! $obj) {
            throw new HttpException(422, "记录不存在或已经删除");
        }

        if ( ! $this->adminUser) {
            $adminUser = Admin::user();
            $this->adminUser = $adminUser;
        } else {
            $adminUser = $this->adminUser;
        }

        //过滤数据:只能查看自己主体或者子主体的数据;项目拥有者可以查看全部
        if ( ! $adminUser->isOwner()) {
            if ($this->dataViewMode == 'all') {
                // 根据账号所属的总公司,显示其下全部主体的数据
                $subject = $adminUser->subject;
                $baseSubject = $subject->baseSubject();

                if ($baseSubject && $baseSubject->base) {
                    $tempSubjectIds = $baseSubject->getChildrenSubject();
                    $subjectIds = $tempSubjectIds;
                } else {
                    throw new HttpException(422, "没有父级总公司主体,无法查看,请检查设置");
                }
            } else {
                //如果设置了manager_subject_ids,则优先处理该值
                //可以查看manager_subject_ids设置范围内的所有主体

                $managerSubjectIds = $adminUser->manager_subject_ids;

                if ( ! empty($managerSubjectIds)) {
                    $tempSubject = new Subject();
                    $tempSubjectIds = $managerSubjectIds;

                    foreach ($managerSubjectIds as $managerSubjectId) {
                        $tempSubjectIds = array_merge($tempSubjectIds,
                            $tempSubject->getChildrenSubject($managerSubjectId));
                    }
                    $subjectIds = array_unique($tempSubjectIds);
                } else {
                    $subject = SubjectUtils::getSubject();
                    $subjectIds = $subject->getChildrenSubject();
                }
            }

            $tableName = $model->getTable();
            if ($tableName == "subjects") {
                //如果访问的subject的id属于$subjectIds可以访问
                if ( ! in_array($id, $subjectIds)) {
                    throw new HttpException(403, "没有权限查看");
                }
            } elseif (Schema::hasColumn($tableName, "subject_id")) {
                if ( ! $model->whereIn('subject_id', $subjectIds)->where('id', $id)->exists()) {
                    throw new HttpException(403, "没有权限查看");
                }
            }
        }
    }

}
