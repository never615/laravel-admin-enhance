<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;


use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\SubjectUtils;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AdminCommonController extends Controller
{
    use ModelForm, AdminOption;


    protected $currentId;

    /**tK
     * 是否关闭了列表显示id和时间列
     *
     * @var bool
     */
    protected $closeDefault = false;

    /**
     * 数据查看模式:
     * 1.根据账号的所属的subject和数据的subject动态显示
     *    标志: dynamic
     * 2.根据账号所属的总公司,显示其下全部主体的数据
     *    (针对一些模块适用于:只要拥有这个模块的权限,就可以看到所属总公司下全部数据的情况)
     *    标志: all
     *
     * @var string
     */
    protected $dataViewMode = 'dynamic';

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header($this->getHeaderTitle());
            $content->description($this->getIndexDesc());
            $content->body($this->grid()->render());
        });
    }


    protected function getIndexDesc()
    {
        return trans('admin.list');
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        $this->currentId = $id;

        //过滤数据:只能查看自己主体或者子主体的数据;项目拥有者可以查看全部
        if (!Admin::user()->isOwner()) {
            if ($this->dataViewMode == 'all') {
                // 根据账号所属的总公司,显示其下全部主体的数据
                $subject = Admin::user()->subject;
                $baseSubject = $subject->baseSubject();

                if ($baseSubject && $baseSubject->base) {
                    $tempSubjectIds = $baseSubject->getChildrenSubject();
                    $subjectIds = $tempSubjectIds;
                } else {
                    throw new HttpException(422,"没有父级总公司主体,无法查看,请检查设置");
                }
            } else {
                //如果设置了manager_subject_ids,则优先处理该值
                $adminUser = Admin::user();
                $managerSubjectIds = $adminUser->manager_subject_ids;

                if (!empty($managerSubjectIds)) {
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

            $model = resolve($this->getModel());
            $tableName = $model->getTable();
            if ($tableName == "subjects") {
                //如果访问的subject的id属于$subjectIds可以访问
                if (!in_array($this->currentId, $subjectIds)) {
                    throw new HttpException(403,"没有权限查看");
                }
            } elseif (Schema::hasColumn($tableName, "subject_id")) {
                if (!$model->whereIn('subject_id', $subjectIds)->where('id', $id)->exists()) {
                    throw new HttpException(403,"没有权限查看");
                }
            }
        }

        return Admin::content(function (Content $content) use ($id) {
            $content->header($this->getHeaderTitle());
            $content->description(trans('admin.edit'));
            $content->body($this->form()->edit($id));
        });
    }


    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header($this->getHeaderTitle());
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }


    protected function grid()
    {
        return Admin::grid($this->getModel(), function (Grid $grid) {
            $this->defaultGridOption($grid);
        });
    }

    protected function defaultGridOption(Grid $grid)
    {
        $tableName = $grid->model()->getTable();
        $adminUser = Admin::user();

        if (!$this->closeDefault) {
            $grid->id('ID')->sortable();
        }

        if (!Admin::user()->isOwner()) {
            if ($this->dataViewMode == 'all') {
                //根据账号所属的总公司,显示其下全部主体的数据
                $subject = $adminUser->subject;
                $baseSubject = $subject->baseSubject();

                if ($baseSubject && $baseSubject->base) {
                    $tempSubjectIds = $baseSubject->getChildrenSubject();
                } else {
                    throw new HttpException(422,"没有父级总公司主体,无法查看,请检查设置");
                }

            } else {
                if (method_exists($this->getModel(), "scopeDynamicData")) {
                    //如果设置了manager_subject_ids,则优先处理该值
                    $managerSubjectIds = $adminUser->manager_subject_ids;
                    if (!empty($managerSubjectIds)) {
                        $tempSubject = new Subject();
                        $tempSubjectIds = $managerSubjectIds;

                        foreach ($managerSubjectIds as $managerSubjectId) {
                            $tempSubjectIds = array_merge($tempSubjectIds,
                                $tempSubject->getChildrenSubject($managerSubjectId));
                        }
                        $tempSubjectIds = array_unique($tempSubjectIds);
                    } else {
                        $currentSubject = $adminUser->subject;
                        $tempSubjectIds = $currentSubject->getChildrenSubject();
                    }
                } else {
                    throw new HttpException(500,"系统错误,未配置scopeDynamicData");
                }
            }

            if ($tableName == "subjects") {
                $grid->model()->whereIn("id", $tempSubjectIds);
            } elseif (Schema::hasColumn($tableName, "subject_id")) {
                $grid->model()->whereIn("subject_id", $tempSubjectIds);
            }

        }

//        $grid->disableExport();

        $this->gridOrder($grid);

        $this->gridOption($grid);


        if (Schema::hasColumn($tableName, "subject_id")) {
            //拥有子主体的主体,在table中增加该字段
            if (Admin::user()->subject->hasChildrenSubject()) {
                $grid->subject_id()->sortable()->display(function ($value) {
                    return $this->getModel()->subject()->first()->name;
                });

//                $grid->subject()->name("所属主体");
            }
        }
        if (!$this->closeDefault) {
            $grid->created_at(trans('admin.created_at'))->sortable();
        }
//        $grid->updated_at(trans('admin.updated_at'))->sortable();

        $grid->filter(function ($filter) {
            // 禁用id查询框
            $filter->disableIdFilter();
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });
    }


    protected function form()
    {
        return Admin::form($this->getModel(), function (Form $form) {
            $this->defaultFormOption($form);
        });
    }

    protected function defaultFormOption(Form $form)
    {
        $tableName = $form->model()->getTable();
        $form->display('id', 'ID');
        $this->formOption($form);

        if (Schema::hasColumn($tableName, "subject_id")) {
            if (!Admin::user()->isOwner() && $this->dataViewMode == 'all') {
                $form->display("subject.name", "总主体");
                $form->hideFieldsByCreate("subject.name");
            } elseif (Admin::user()->isOwner() && $this->dataViewMode == 'all') {
                $form->select("subject_id", "总主体")
                    ->options(Subject::where("base", true)
                        ->pluck("name", "id"));
            } else {
                $form->select("subject_id", "主体")
                    ->default(Admin::user()->subject->id)
                    ->options(function () {
                        $currentSubjectId = $this->subject_id;
                        $currentSubject = Subject::find($currentSubjectId);
                        $subjects = Subject::dynamicData()->pluck("name", "id");
                        if ($currentSubject) {
                            $subjects = array_add($subjects, $currentSubject->id, $currentSubject->name);
                        }

                        return $subjects;
                    })->rules("required");
            }
        }

        if (Schema::hasColumn($tableName, "admin_user_id")) {
            $form->display('admin_user_id', "操作人")->with(function ($value) {
                $adminUser = Administrator::find($value);

                return $adminUser ? $adminUser->name : "";
            });

            $form->hideFieldsByCreate(["admin_user_id"]);
        }

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));
    }


    /**
     * 自动设置subjectId
     *
     * @param $form
     */
    protected function autoSubjectSet($form)
    {
        //什么账号创建就是谁的总部的
        $subject = Admin::user()->subject;
        $baseSubject = $subject->baseSubject();
        if ($baseSubject && $baseSubject->base) {
            $form->model()->subject_id = $baseSubject->id;
        }
    }

    /**
     * 自动设置adminUser
     *
     * @param $form
     */
    protected function autoAdminUser($form)
    {
        $adminUser = Admin::user();
        $tableName = $form->model()->getTable();
        if (Schema::hasColumn($tableName, "admin_user_id")) {
            $form->model()->admin_user_id = $adminUser->id;
        }

    }

    protected function gridOrder($grid)
    {
        $grid->model()->orderBy('id');
    }

}
