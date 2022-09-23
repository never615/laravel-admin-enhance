<?php
/**
 * Copyright (c) 2019. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Arr;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\OperationLog;
use Mallto\Admin\Data\OperationLogDictionary;

class OperationLogController extends AdminCommonController
{

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return OperationLog::class;
    }


    protected function getHeaderTitle()
    {
        return "操作日志";
    }


    protected function gridOption(Grid $grid)
    {
        $adminUser = Admin::user();
        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', 'ID')->sortable();
        $grid->column('user.name', '操作人');
        $grid->column('method', '行为')->display(function ($method) {
            //判断增删改查
            if ($method === 'GET') {
                $method = '查看';
                if (strpos($this->path, 'create') !== false) {
                    $method = '创建';
                }
                if (strpos($this->path, 'edit') !== false) {
                    $method = '查看详情';
                }
            } elseif ($method === 'POST') {
                $method = '保存';
            } elseif ($method === 'PUT') {
                $method = '更新';
            } elseif ($method === 'DELETE') {
                $method = '删除';
            }

            return $method;
        });
        $grid->column('path', '请求名称')->display(function ($value) {
            //新增处理
            if (strpos($this->path, 'create') !== false) {
                $value = str_replace('/create', '', $value);
            }

            //查看详情处理
            if (strpos($this->path, 'edit') !== false) {
                $str_value = str_replace('/edit', '', $value);
                $str_len_value = strrpos($str_value, '/');
                $value = substr($str_value, 0, $str_len_value);
            }

            //更新处理
            if ($this->method === 'PUT' || $this->method === 'DELETE') {
                $str_len_value = strrpos($value, '/');
                $value = substr($value, 0, $str_len_value);
            }

            $operationLogDictionary = OperationLogDictionary::query()
                ->where('path', '~*', $value)
                ->first();

            return $operationLogDictionary->name ?? $value;
        })->label('info');
        $grid->column('ip')->label('primary');
        if ($adminUser->isOwner()) {
            $grid->column('api_path', 'api路径')->display(function ($api_path) {
                return $this->path ?? null;
            })->label('info');
            $grid->column('input')->display(function ($input) {
                $input = json_decode($input, true);
                if ( ! is_array($input)) {
                    return '<code>{}</code>';
                }
                $input = Arr::except($input, [ '_pjax', '_token', '_method', '_previous_' ]);

                return '<pre>' . json_encode($input, JSON_PRETTY_PRINT | JSON_HEX_TAG) . '</pre>';
            });
        }

        $grid->column('created_at', trans('admin.created_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->disableCreateButton();

        $grid->filter(function (Grid\Filter $filter) {
            $userModel = config('admin.database.users_model');

            $filter->equal('user_id', 'User')->select($userModel::all()->pluck('name', 'id'));
            $filter->equal('method')->select(array_combine(OperationLog::$methods, OperationLog::$methods));
            $filter->like('path');
            $filter->equal('ip');
        });
    }


    /**
     * 需要实现的form设置
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     *
     * 需要判断当前环境是edit还是create可以通过$this->currentId是否存在来判断,$this->currentId存在即edit时期.
     *
     * 如果需要分开实现create和edit表单可以通过$this->currentId来区分
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function formOption(Form $form)
    {
        $form->text("name");
        $form->text("path");
    }
}
