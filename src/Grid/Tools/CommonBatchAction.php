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
     * @var string
     */
    protected $swalTitle;

    /**
     * @var boolean
     */
    protected $isSwal;


    /**
     * CommonBatchAction constructor.
     *
     * @param string $url    请求地址
     * @param string $action 动作标识,用来区分不同的提交
     * @param string $column 要修改的字段
     * @param bool   $isSwal 是否弹窗
     * @param string $title  弹窗标题
     */
    public function __construct($url, $action = "", $column = "", $isSwal = false, $swalTitle = "是否进行批量处理")
    {
        $this->url = $url;
        $this->action = $action;
        $this->column = $column;
        $this->isSwal = $isSwal;
        $this->swalTitle = $swalTitle;
    }


    public function script()
    {
        $tempUrl = "";
        $isSwal = '';
        if ($this->isSwal) {
            $isSwal = 'true';
        }

        if (starts_with($this->url, "http")) {
            $tempUrl = $this->url;
        } else {
            $tempUrl = $this->resource . "/" . $this->url;
        }

        return <<<EOT
$('{$this->getElementClass()}').on('click', function() {
                console.log("click");
                
        var ids = $.admin.grid.selected();
        if (ids.length < 1) {
            sweetAlert("最少需要选择一个条目");
        }else{
            if ("{$isSwal}") {
                swal({
                    title: "{$this->swalTitle}",
                    confirmButtonText: "确认",
                    showLoaderOnConfirm: true,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "确认",
                    cancelButtonText: "取消"
                }).then(function (result) {
                    if (result.value) {
                        doAjax("{$tempUrl}", "POST", {
                                _token: LA.token,
                                ids: ids,
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
            } else {
                doAjax("{$tempUrl}", "POST", {
                    _token: LA.token,
                    ids: ids,
                    action: '{$this->action}',
                    type: 'action',
                    column:'{$this->column}'
                }, function (data) {
                    console.log("response");
                    layer.msg('操作成功', {icon: 1});
                    $.pjax.reload('#pjax-container');
                });
            }
        }
});
EOT;

    }

}
