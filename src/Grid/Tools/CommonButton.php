<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

/**
 * example:
 * $grid->tools(function (Grid\Tools $tools) {
 *      $tools->append(new CommonButton("库存调整", admin_url("customer_service_goods_remains")));
 * });
 *
 *
 * Class CommonButton
 *
 * @package Mallto\Admin\Grid\Tools
 */
class CommonButton extends AbstractTool
{

    private $name;

    private $url;


    /**
     * CommonButton constructor.
     *
     * @param $name
     * @param $url
     */
    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
    }


    /**
     * Script for this tool.
     *
     * @return string
     */
    protected function script()
    {
        return <<<EOT


    $('.mt-grid-common').on("click",function(){
        var ids = $.admin.grid.selected();
        if (ids.length != 1) {
            sweetAlert("需要选择一个条目");
        }else{
            var id = ids[0];
            window.open("$this->url?id="+id,"_blank");
        }
    });


EOT;
    }


    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render()
    {
        Admin::script($this->script());

        return <<<EOT
<a class="btn btn-sm btn-primary mt-grid-common" target="_blank"> $this->name</a>


EOT;
    }
}
