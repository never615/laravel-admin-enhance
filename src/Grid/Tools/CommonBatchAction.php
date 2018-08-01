<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

/**
 *
 * 这个一般用来改变值的状态
 *
 * example:
 *
 * $grid->tools(function (Grid\Tools $tools) {
 *      $tools->append(new CommonButton("积分增减", admin_url("member_points")));
 *      $tools->batch(function (Grid\Tools\BatchActions $batchs) {
 *          $batchs->add("挂失", new CommonBatchAction("member_card_status", self::LOSS_CARD_ACTION));
 *          $batchs->add("解挂", new CommonBatchAction("member_card_status", self::FIND_CARD_ACTION));
 *      });
 * });
 *
 *
 *
 * Class CommonBatchAction
 *
 * @package Mallto\Admin\Grid\Tools
 */
class CommonBatchAction extends BatchAction
{
    private $url;
    /**
     * @var string
     */
    private $action;
    /**
     * @var string
     */
    private $column;

    /**
     * CommonBatchAction constructor.
     *
     * @param        $url
     * @param string $action
     * @param string $column
     */
    public function __construct($url, $action = "", $column = "")
    {
        $this->url = $url;
        $this->action = $action;
        $this->column = $column;
    }


    public function script()
    {
        $tempUrl = "";
        if (starts_with($this->url, "http")) {
            $tempUrl = $this->url;
        } else {
            $tempUrl = $this->resource."/".$this->url;
        }

        return <<<EOT
$('{$this->getElementClass()}').on('click', function() {
                console.log("click");
                
        var ids = selectedRows();
        if (ids.length < 1) {
            sweetAlert("最少需要选择一个条目");
        }else{
                doAjax("{$tempUrl}", "POST", {
                    _token: LA.token,
                    ids: selectedRows(),
                    action: '{$this->action}',
                    type: 'action',
                    column:'{$this->column}'
                }, function (data) {
                    console.log("response");
                
                    layer.msg('操作成功', {icon: 1});
                    $.pjax.reload('#pjax-container');
                });
        }

                
});
EOT;

    }

}
