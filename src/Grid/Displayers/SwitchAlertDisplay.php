<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Displayers;

use Encore\Admin\Admin;

/**
 * 开关弹窗
 *
 * Class SwitchAlertDisplay
 *
 * @package Mallto\Admin\Grid\Displayers
 */
class SwitchAlertDisplay extends \Encore\Admin\Grid\Displayers\SwitchDisplay
{

    /**
     * 带弹窗的开关
     *
     * @param array  $states            switch内容数组
     * @param string $title             弹窗标题
     * @param string $text              弹窗文本
     * @param string $confirmButtonText 弹窗确认按钮
     * @param string $cancelButtonText  弹窗取消按钮
     *
     * @return mixed|string
     */
    public function display(
        $states = [],
        $title = '',
        $text = '',
        $confirmButtonText = '确认',
        $cancelButtonText = '取消'
    ) {
        $this->overrideStates($states);

        $name = $this->column->getName();

        $class = "grid-switch-{$name}";

        $script = <<<EOT
        
var isError = false;

$('.$class').bootstrapSwitch({
    size: 'mini',
    onText: '{$this->states['on']['text']}',
    offText: '{$this->states['off']['text']}',
    onColor: '{$this->states['on']['color']}',
    offColor: '{$this->states['off']['color']}',
    onSwitchChange: function (event, state) {

        if (isError) {
            isError = false;
            return;
        }

        $(this).val(state ? 'on' : 'off');
        var that = $(this);

        var pk = $(this).data('key');
        var value = $(this).val();

        //如果按钮开启了,则需要弹窗
        if (value == 'on') {
            swal({
                title: "{$title}",
                text: "{$text}",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "{$confirmButtonText}",
                cancelButtonText: "{$cancelButtonText}",
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: "{$this->grid->resource()}/" + pk,
                        type: "POST",
                        data: {
                            $name: value,
                            _token: LA.token,
                            _method: 'PUT'
                        },
                        success: function (data) {
                            toastr.success(data.message);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            isError = true;
                            var msg = "";
                            if (XMLHttpRequest.responseJSON && XMLHttpRequest.responseJSON.error) {
                                //后台有专门返回的错误信息的情况
                                msg += XMLHttpRequest.responseJSON.error;
                            } else {
                                //错误不是后台专门返回的 422除外
                                if (XMLHttpRequest.status == 422) {
                                    var erroMsg = JSON.parse(XMLHttpRequest.responseText);
                                    $.each(erroMsg, function (k, v) {
                                        msg += v[0] + "\\n";
                                    });
                                } else {
                                    //错误不是后台专门返回的 
                                    msg += XMLHttpRequest.statusText + ":" + XMLHttpRequest.status;
                                }
                            }
                            //拿着msg做出提示
                            notify.alert(3, msg, 3);
                            that.bootstrapSwitch('toggleState');
                        }
                    });
                } else {
                    //第一个参数是开关属性
                    //第二个参数是对第一个属性设置开/关，true：开，false：关
                    //第三个参数是对于设置开关状态是否让其再次触发change事件；true：不让触发change事件，false：让其触发change事件（默认）
                    //当点击取消的时候关闭switch的选择
                    that.bootstrapSwitch('state', false, true);
                }
            });
        } else {
            $.ajax({
                url: "{$this->grid->resource()}/" + pk,
                type: "POST",
                data: {
                    $name: value,
                    _token: LA.token,
                    _method: 'PUT'
                },
                success: function (data) {
                    toastr.success(data.message);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    isError = true;
                    var msg = "";
                    if (XMLHttpRequest.responseJSON && XMLHttpRequest.responseJSON.error) {
                        //后台有专门返回的错误信息的情况
                        msg += XMLHttpRequest.responseJSON.error;
                    } else {
                        //错误不是后台专门返回的 422除外
                        if (XMLHttpRequest.status == 422) {
                            var erroMsg = JSON.parse(XMLHttpRequest.responseText);
                            $.each(erroMsg, function (k, v) {
                                msg += v[0] + "\\n";
                            });
                        } else {
                            //错误不是后台专门返回的 
                            msg += XMLHttpRequest.statusText + ":" + XMLHttpRequest.status;
                        }
                    }
                    //拿着msg做出提示
                    notify.alert(3, msg, 3);
                    that.bootstrapSwitch('toggleState');
                }
            });
        }
    }
});
EOT;

        Admin::script($script);

        $key = $this->row->{$this->grid->getKeyName()};

        $checked = $this->states['on']['value'] == $this->value ? 'checked' : '';

        return <<<EOT
        <input type="checkbox" class="$class" $checked data-key="$key" />
EOT;
    }
}
