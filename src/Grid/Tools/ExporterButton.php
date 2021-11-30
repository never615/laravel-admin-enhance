<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

/**
 * Class RefreshButton
 *
 * @package Mallto\Admin\Grid\Tools
 */
class ExporterButton extends AbstractTool
{

    /**
     * @var $name
     */
    private $name;

    /**
     * @var $url
     */
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
            $('.mt-grid-refresh').on("click",function(){
                var r =confirm('确认导出会员信息吗?');
                if(r==true){
                    var url = '';
                    var str = window.location.href;
                    var num=str.indexOf("&")
                    if(num != -1)
                    {
                      url=str.substr(num+1);
                    }
                    window.open("{$this->url}"+'?'+url,'_blank');
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
<a class="btn btn-sm btn-primary mt-grid-refresh" target="_blank"> $this->name</a>

EOT;
    }
}
