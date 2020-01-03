<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

/**
 * 这个用来修改时间(datetime)值
 *
 * Class DateTimeBatchAction
 *
 * @package Mallto\Admin\Grid\Tools
 */
class DateTimeBatchAction extends BatchAction
{

    private $text;

    private $url;

    private $column;


    /**
     * CommonBatchAction constructor.
     *
     * @param string $text
     * @param        $url
     * @param        $column
     */
    public function __construct($text = "时间", $url, $column)
    {
        $this->text = $text;
        $this->url = $url;
        $this->column = $column;
    }


    public function script()
    {


        $tempUrl = "";
        if (starts_with($this->url, "http")) {
            $tempUrl = $this->url;
        } else {
            $tempUrl = $this->resource . "/" . $this->url;
        }

        //selectedRows()
        $locale = config('app.locale');
        $layDateId = "laydate_" . $this->id;

        return <<<EOF
$('{$this->getElementClass()}').on('click', function() {
console.log("111");
    var ids = $.admin.grid.selected();
    if (ids.length < 1) {
        sweetAlert("最少需要选择一个条目");
    }else{
        layer.open({
        type: 1,
        title: false,
        skin: 'layui-layer-demo', //样式类名
        closeBtn: 1, //不显示关闭按钮
        anim: 2,
        area: ['500px', '400px'], //宽高
        shadeClose: true, //开启遮罩关闭
        content: '<div style="margin: 10px; margin-top: 20px">{$this->text}: <input type="text" id="{$layDateId}"/><\/div>',
        btn:["确认"] ,
        yes: function(index){
                 var tempDatetime=$('#{$layDateId}').val();
                 console.log(tempDatetime);
                 doAjax("{$tempUrl}", "POST", {
                     _token: LA.token,
                     ids: ids,
                     type: 'datetime',
                     column:"{$this->column}",
                     date: tempDatetime
                 }, function (data) {
                     layer.close(index);
                     $.pjax.reload('#pjax-container');
                     layer.msg('操作成功', {icon: 1});
                 });
            }      
        });
    }

    
$('#{$layDateId}').datetimepicker({
    format:'YYYY-MM-DD HH:mm:ss',
    locale:'{$locale}',
    allowInputToggle:true,
    inline: true,
});
});
EOF;


    }

}
