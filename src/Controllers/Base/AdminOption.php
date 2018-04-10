<?php
/**
 * Copright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Encore\Admin\Form;
use Encore\Admin\Grid;

/**
 * Created by PhpStorm.
 * User: never615
 * Date: 08/03/2017
 * Time: 3:05 PM
 */
trait AdminOption
{
    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected abstract function getHeaderTitle();

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected abstract function getModel();

    protected abstract function gridOption(Grid $grid);

    protected abstract function formOption(Form $form);
}
