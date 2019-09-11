<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;


use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Exporter;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Schema;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Traits\AdminFileHelp;
use Mallto\Tool\Exception\PermissionDeniedException;

abstract class AdminCommonController extends AdminController
{
    use  AdminOption, AdminSubjectTrait, AdminUserTrait, AdminFilterData, AdminFileHelp;


    /**
     * 编辑或者查看详情时,当前条目的id,不存在则表示是创建模式
     *
     * @var
     */
    protected $currentId;

    /**
     * 是否关闭了列表显示id和时间列
     *
     * true表示关闭,false表示不关闭
     *
     * @var bool
     */
    protected $closeGridUpdatedAt = true;


    /**
     * 默认的过滤器是否显示
     *
     * @var bool
     */
    protected $defaultFilter = true;

    /**
     * 表格created_at是否显示
     *
     * @var bool
     */
    protected $closeGridCreatedAt = false;

    /**
     * 表名
     *
     * @var
     */
    protected $tableName;

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
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $grid = $this->grid();
        if (config('admin.swoole') && request(Exporter::$queryName)) {
            return $grid->handleExportRequest();
        }

        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }


    /**
     * Edit interface.
     *
     * @param         $id
     *
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $this->currentId = $id;

        $this->editFilterData();


        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form()->edit($id));
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return redirect(request()->url()."/edit");
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->currentId = $id;

        return parent::update($id);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->currentId = $id;

        return parent::destroy($id);
    }


    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form());
    }


    protected function form()
    {
        return Admin::form($this->getModel(), function (Form $form) {
            $this->tableName = $this->getTableName();

            $this->formShopFilterInter($form);

            $this->defaultFormOption($form);
            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });
        });
    }


    protected function grid()
    {
        return Admin::grid($this->getModel(), function (Grid $grid) {
            $this->tableName = $grid->model()->getTable();
            $this->defaultGridOption($grid);
        });
    }

    protected function defaultGridOption(Grid $grid)
    {
        $this->gridShopFilterInter($grid);

        $grid->expandFilter();

        $filter = $grid->getFilter();

        $isOwner = AdminUtils::isOwner();

        if (!$isOwner) {
            $filter->disableIdFilter();
        } else {
            $grid->id('ID')->sortable();
            if (Schema::hasColumn($this->tableName, "subject_id")) {
                $filter->equal("subject_id", "主体")->select(Subject::selectSourceDate());
            }
        }

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });


        $this->gridModelFilter($grid);
        $this->gridFilterData($grid);
        $this->gridOrder($grid);
        $this->gridOption($grid);
        $this->gridSubject($grid);

        $grid->filter(function (Grid\Filter $filter) {
            if ($this->defaultFilter) {
                $this->gridAdminUserFilter($filter);

                $filter->between("created_at")->datetime();
            }
        });
        if (!$this->closeGridCreatedAt) {
            $grid->created_at(trans('admin.created_at'))->sortable();
        }
        if (!$this->closeGridUpdatedAt) {
            $grid->updated_at(trans('admin.updated_at'))->sortable();
        }
    }


    /**
     * 默认的form实现,create的表单页面和edit的表单页面同时会调用到这里
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * 如果form中使用到了tab,需要复写此方法
     *
     * @param Form $form
     */
    protected function defaultFormOption(Form $form)
    {
        $form->displayE('id', 'ID');

        $form->saving(function ($form) {
            $this->autoSubjectSaving($form);
            $this->autoAdminUserSaving($form);
        });

        $this->formOption($form);
        $this->formSubject($form);
        $this->formAdminUser($form);
        $form->displayE('created_at', trans('admin.created_at'));
        $form->displayE('updated_at', trans('admin.updated_at'));
    }


    protected function gridAdminUserFilter($filter)
    {
        if (Schema::hasColumn($this->tableName, "admin_user_id")) {
            $filter->equal("admin_user_id", "操作人")->select(Administrator::selectSourceDatas());
        }
    }

    /**
     * 默认的排序,重写此方法覆盖
     * 没有默认排序的话出来都是乱的
     *
     * @param $grid
     */
    protected function gridOrder($grid)
    {
        $grid->model()->orderBy('id', 'desc');
    }


    /**
     * 自定义的列表过滤数据
     *
     * @param $grid
     */
    protected function gridModelFilter($grid)
    {
        //$grid->model()->where("type", "park");
    }


    /**
     * 列表店铺权限检查/数据过滤
     *
     * @param $grid
     */
    protected function gridShopFilterInter($grid)
    {
        //默认店铺账号不能查看任何数据,除非该模块专门代码处理进行支持
        $adminUser = Admin::user();
        $adminiableType = $adminUser->adminable_type;
        if ($adminiableType) {
            switch ($adminiableType) {
                case "subject":
                    //运营者只能查看指定范围的租户投诉建议,根据分配的店铺分组过滤,如果分组没设置则可以查看全部
                    $managerShopGroups = $adminUser->shop_groups;
                    if (Schema::hasColumn($this->tableName, "shop_id")) {
                        if (!empty($managerShopGroups)) {
                            //当前数据有店铺id且当前登录账号设置了店铺数据查看范围
                            //判断model是否有shop()方法
                            if (method_exists($this->getModel(), 'shop')) {
                                $grid->model()->whereHas("shop", function ($query) use ($managerShopGroups) {
                                    $query->whereHas("groups", function ($query) use ($managerShopGroups) {
                                        $query->whereIn("shop_groups.id", $managerShopGroups);
                                    });
                                });
                            }
                        }
                    } elseif ($this->tableName == "shops") {
                        //特别处理shops表的数据过滤
                        if (!empty($managerShopGroups)) {
                            $grid->model()->whereHas("groups", function ($query) use ($managerShopGroups) {
                                $query->whereIn("id", $managerShopGroups);
                            });
                        }
                    }
                    break;
                case "shop":
                    $this->gridShopAccountFilter($grid);
                    break;
                default:
                    throw new PermissionDeniedException("非主体账号无权限查看");
                    break;
            }

        } else {
            throw new PermissionDeniedException("非主体账号无权限查看");
        }
    }

    /**
     * 详情页的店铺权限检查/数据过滤
     *
     * 运营者只能查看指定范围的租户投诉建议,根据分配的店铺分组过滤,如果分组没设置则可以查看全部
     *
     * @param $form
     */
    protected function formShopFilterInter($form)
    {
        //默认店铺账号不能查看任何数据,除非该模块专门代码处理进行支持
        $adminUser = Admin::user();
        $adminiableType = $adminUser->adminable_type;
        if ($adminiableType) {
            switch ($adminiableType) {
                case "subject":
                    //运营者只能查看指定范围的租户投诉建议,根据分配的店铺分组过滤,如果分组没设置则可以查看全部
                    $managerShopGroups = $adminUser->shop_groups;
                    if (Schema::hasColumn($this->tableName, "shop_id")) {
                        if (!empty($managerShopGroups)) {
                            //当前数据有店铺id且当前登录账号设置了店铺数据查看范围
                            //todo 根据$this->currentId判断

                        }
                    } elseif ($this->tableName == "shops") {
                        //特别处理shops表的数据过滤
                        //todo 根据$this->currentId判断

                    }
                    break;
                case "shop":
                    $this->formShopAccountFilter($form);
                    break;
                default:
                    throw new PermissionDeniedException("非主体账号无权限查看");
                    break;
            }

        } else {
            throw new PermissionDeniedException("非主体账号无权限查看");
        }
    }




    /**
     * 店铺账号列表权限检查
     *
     * @param $grid
     */
    protected function gridShopAccountFilter($grid)
    {
        throw new PermissionDeniedException("非主体账号无权限查看");
    }


    /**
     * 店铺账号详情页全新检查
     *
     * @param $form
     */
    protected function formShopAccountFilter($form)
    {
        throw new PermissionDeniedException("非主体账号无权限查看");
    }


    protected function getTableName()
    {
        if (!$this->tableName) {
            $model = resolve($this->getModel());
            $this->tableName = $model->getTable();
        }


        return $this->tableName;
    }

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        $this->title = admin_translate($this->getTableName(), "table");

        return $this->title;
    }

    /**
     * Get content title.
     *
     * laravel-admin后来新增的方法,因为我已经有了getHeaderTitle且大量子类使用,所以继续使用getHeaderTitle
     *
     * @return string
     */
    protected function title()
    {
        return $this->getHeaderTitle();
    }

}
