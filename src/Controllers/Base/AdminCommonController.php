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
use Mallto\Admin\Data\Subject;
use Mallto\Mall\Data\AdminUser;
use Mallto\Tool\Exception\PermissionDeniedException;

abstract class AdminCommonController extends Controller
{
    use ModelForm, AdminOption, AdminSubjectTrait, AdminUserTrait, AdminFilterData;


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
    protected $closeIdAndTime = false;


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

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        $model = resolve($this->getModel());
        $tableName = $model->getTable();

        return admin_translate($tableName, $tableName);
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

        $this->editFilterData();

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


    protected function form()
    {
        return Admin::form($this->getModel(), function (Form $form) {
            $this->tableName = $form->model()->getTable();;
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

        $this->gridShopFilter($grid);

        $grid->expandFilter();

        $adminUser = Admin::user();

        $filter = $grid->getFilter();

        if (!$adminUser->isOwner()) {
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
            if (Schema::hasColumn($this->tableName, "admin_user_id")) {
                $filter->equal("admin_user_id", "操作人")->select(AdminUser::selectSourceDatas());
            }

            $filter->between("created_at")->date();
        });

        if (!$this->closeIdAndTime) {
            $grid->created_at(trans('admin.created_at'))->sortable();
            //$grid->updated_at(trans('admin.updated_at'))->sortable();
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
        $this->formShopFilter($form);

        $form->display('id', 'ID');

        $form->saving(function ($form) {
            $this->autoSubjectSaving($form);
            $this->autoAdminUserSaving($form);
        });

        $this->formOption($form);

        $this->formSubject($form);
        $this->formAdminUser($form);
        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));
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
    protected function gridShopFilter($grid)
    {
        $this->shopFilter();
    }

    /**
     * 表单页面店铺权限检查/数据过滤
     *
     * @param $form
     */
    protected function formShopFilter($form)
    {
        $this->shopFilter();
    }

    protected function shopFilter()
    {
        //默认店铺账号不能查看任何数据,除非该模块专门代码处理进行支持
        $adminUser = Admin::user();
        $adminiableType = $adminUser->adminable_type;
        if ($adminiableType) {
            switch ($adminiableType) {
                case "subject":
                    //pass
                    break;
                default:
                    throw new PermissionDeniedException("非主体账号无权限查看");
                    break;
            }

        } else {
            throw new PermissionDeniedException("非主体账号无权限查看");
        }
    }

}
