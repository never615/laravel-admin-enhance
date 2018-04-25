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


    protected function grid()
    {
        return Admin::grid($this->getModel(), function (Grid $grid) {
            $this->tableName = $grid->model()->getTable();
            $this->defaultGridOption($grid);
        });
    }

    protected function defaultGridOption(Grid $grid)
    {
        if (!$this->closeIdAndTime) {
            $grid->id('ID')->sortable();
        }

        $this->gridFilterData($grid);
        $this->gridOrder($grid);
        $this->gridOption($grid);
        $this->gridSubject($grid);

        if (!$this->closeIdAndTime) {
            $grid->created_at(trans('admin.created_at'))->sortable();
            //$grid->updated_at(trans('admin.updated_at'))->sortable();
        }

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
            $this->tableName = $form->model()->getTable();;
            $this->defaultFormOption($form);
        });
    }

    protected function defaultFormOption(Form $form)
    {
        $form->display('id', 'ID');
        $this->formOption($form);

        $this->formSubject($form);
        $this->formAdminUser($form);
        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));


        $form->saving(function ($form) {
            $this->autoSubjectSaving($form);
            $this->autoAdminUserSaving($form);
        });
    }


    /**
     * 默认的排序,重写此方法覆盖
     * 没有默认排序的话出来都是乱的
     *
     * @param $grid
     */
    protected function gridOrder($grid)
    {
        $grid->model()->orderBy('id');
    }

}
