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

//    /**
//     * 获取这个模块的标题
//     *
//     * @return mixed
//     */
//    protected abstract function getHeaderTitle();

    /**
     * 获取模块的副标题
     *
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    protected function getIndexDesc()
    {
        return trans('admin.list');
    }


    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected abstract function getModel();


    protected abstract function gridOption(Grid $grid);


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
    protected abstract function formOption(Form $form);
}
