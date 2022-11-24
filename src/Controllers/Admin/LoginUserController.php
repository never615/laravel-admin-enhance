<?php
/*
 * Copyright (c) 2022. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Carbon;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Session;

/**
 * User: never615 <never615.com>
 * Date: 2022/11/16
 * Time: 1:27 AM
 */
class LoginUserController extends AdminCommonController
{

    protected $dataViewMode = 'all';

    /**
     * 表格created_at是否显示
     *
     * @var bool
     */
    protected $closeGridCreatedAt = true;

    /**
     * 默认的过滤器是否显示
     *
     * @var bool
     */
    protected $defaultFilter = false;


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return '在线账号';
    }


    protected function getModel()
    {
        return Session::class;
    }


    protected function gridOption(Grid $grid)
    {

        $grid->model()->whereNotNull('user_id');

        $grid->disableCreateButton();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->user_id('账户名称')
            ->sortable()
            ->display(function ($value) {
                $adminUser = Administrator::find($value);

                return $adminUser ? $adminUser->name : '';
            })
            ->help('点击行尾的删除即可令账号下线')
            ->linkE(function () {
                return '/admin/auth/admins/' . $this->row->user_id . '/edit';
            });

        $grid->ip_address('ip')
            ->sortable();

        $grid->last_activity('最近活动时间')
            ->sortable()
            ->display(function ($value) {
                return Carbon::createFromTimestamp($value)->toDateTimeString();
            });

        if (AdminUtils::isOwner()) {
            $grid->user_agent('user_agent');
        }

        //$grid->user()
        //    ->name();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->equal("user_id",
                "账户名称")->select(\Mallto\Admin\Data\Administrator::selectSourceDatas());
        });
    }


    protected function formOption(Form $form)
    {
        // TODO: Implement formOption() method.
    }
}
