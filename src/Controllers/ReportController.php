<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Report;
use Mallto\Mall\Domain\Traits\AdminHelp;

class ReportController extends AdminCommonController
{

    use AdminHelp;

    protected $closeGridUpdatedAt = false;


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "数据报表";
    }


    protected function getIndexDesc()
    {
        return "管理";
    }


    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Report::class;
    }


    protected function gridOption(Grid $grid)
    {
        $adminUser = Admin::user();

        if ( ! AdminUtils::isOwner()) {
            $permissions = $adminUser->roles()->with('permissions')->get()->pluck('permissions')->flatten();

            $tables = [];
            foreach ($permissions as $permission) {
                foreach ($permission->subPermissions() as $subPermission) {
                    $tablesExplode = explode('.', $subPermission['slug']);

                    if (count($tablesExplode) > 1 && ($tablesExplode[1] == 'export')) {
                        $tables[] = $tablesExplode[0];
                    }
                }
            }

            $otherTables = [];

            if ($adminUser->can('member_vip_status_records.export')) {
                $otherTables[] = 'member_vip_pay_records';
            }

            if ($adminUser->can('members.export')) {
                $otherTables[] = 'users';
            }

            if ($adminUser->can('orders')) {
                $otherTables[] = 'user_order';//线下交易数据会员导出
            }

            $grid->model()->whereIn('table_name', array_merge($otherTables, $tables));
        }

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->name();
        $grid->finish()->display(function ($finish) {
            return $finish == true ? "是" : "否";
        });

        $grid->column('now_percentage', '当前导出进度')
            ->display(function ($value) {
                if ($this->status === '任务失败') {
                    return $value;
                }

                if ($this->status === '已完成') {
                    return 100;
                }

                return $value * 100;
            })
            ->progressBar($style = 'primary', $size = 'sm', $max = 100);

        //$url = $disk->privateDownloadUrl('folder/my_file.txt');
        $grid->status();
        $grid->column("download")->display(function () {
            if ($this->finish === true) {
                $disk = Storage::disk("qiniu_private");
                $url = $disk->privateDownloadUrl(config("app.unique") . '/' . config("app.env") . '/exports/' . $this->name,
                    60);

                return <<<EOT
                <a href="$url" target="_blank">点击下载</a>
EOT;
            } else {
                return "";
            }

        });
        $grid->subject()->name("主体");
        $grid->adminUser()->name("创建人");
        $grid->desc();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->filter(function (Grid\Filter $filter){
           $this->gridAdminUserFilter($filter);
        });


    }


    protected function formOption(Form $form)
    {
    }
    //todo 删除事件->删除对应文件

}
