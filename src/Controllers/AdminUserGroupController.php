<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\AdminUserGroup;

class AdminUserGroupController extends AdminCommonController
{

    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "管理账户分组";
    }


    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return AdminUserGroup::class;
    }


    protected function gridOption(Grid $grid)
    {
        $grid->name("组名");
//        $grid->slug();
//        $grid->remark();

    }


    /**
     * 需要实现的form设置
     *
     * 如果需要使用tab,则需要复写defaultFormOption()方法,
     * 然后formOption留空即可
     *
     * @param Form $form
     *
     * @return mixed
     */
    protected function formOption(Form $form)
    {
        $form->text("name", "组名");
//        $form->text("slug");
//        $form->text("remark");

        $form->multipleSelect("users", "组内账户")
            ->options(Administrator::selectSourceDatas());


    }
}
