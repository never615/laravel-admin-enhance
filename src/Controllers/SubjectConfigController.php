<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\SubjectUtils;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 2018/12/7
 * Time: 2:53 PM
 */
class SubjectConfigController extends AdminCommonController
{

    /**
     * 动态配置批量删除是否开启
     *
     * @var bool
     */
    protected $isDisableDelete = true;

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return SubjectConfig::class;
    }


    protected function gridOption(Grid $grid)
    {
        $grid->tools(function ($tools) {
            $tools->append('<div class="alert alert-info" style="margin:0 0 10px 0;">动态配置保存后不会立即进入 LaravelS/Horizon 运行期内存。需要进入“配置中心 &gt; 发布与重启”执行发布并重启后，新的定位/推送等长驻进程才会读取最新值。</div>');
        });

        $grid->type();



//        $grid->key('说明')->display(function ($value) {
//            return config('subject-config.subject_config_key')[$value] ?? $value;
//        });

        $grid->key()->editable();
        $grid->value()->editable();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike('key');
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
        $form->select('type')
            ->options(SubjectConfig::TYPE)
            ->default('private')
            ->help('front类型的配置会前端请求的主体初始化配置接口一起返回。保存后需要进入“配置中心 > 发布与重启”执行发布并重启，LaravelS/Horizon 新进程才会读取最新运行期快照。');

        $form->displayE('show_default_key', '预设的一些key')
            ->setDisplay(true)
            ->help('<a href="https://wiki.mall-to.com/web/#/44?page_id=904">更多配置见</a>')
            ->with(function ($values) {
                $html = '<table border="1"><tr><th>说明</th><th>key</th></tr>';
                foreach (config('subject-config.subject_config_key') as $key => $value) {
                    $html .= "<tr><th>$value</th><th>$key</th></tr>";
                }

                return $html . '</table>';
            });

        $form->text('key');

        $form->textarea('value')->rows(15);
        $form->textarea('remark');

        $form->saved(function (Form $form) {
            if ($form->model()->key) {
                SubjectUtils::clearDynamicConfig($form->model()->key, $form->model()->subject_id);
            }
        });
    }
}
