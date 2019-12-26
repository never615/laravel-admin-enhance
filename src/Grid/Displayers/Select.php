<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Displayers;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class Select extends AbstractDisplayer
{

    public function display($options = [])
    {
        $name = $this->column->getName();

        $class = "grid-select-{$name}";

        $script = <<<EOT

$('.$class').select2().on('change', function(){

    var pk = $(this).data('key');
    var value = $(this).val();

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
            isError=true;
            var msg=""; 
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
//            location.reload();
        }        
    });
});

EOT;

        Admin::script($script);

        $key = $this->row->{$this->grid->getKeyName()};

        $optionsHtml = '';

        foreach ($options as $option => $text) {
            $selected = $option == $this->value ? 'selected' : '';
            $optionsHtml .= "<option value=\"$option\" $selected>$text</option>";
        }

        return <<<EOT
<select style="width: 100%;" class="$class btn btn-mini" data-key="$key">
$optionsHtml
</select>

EOT;
    }
}
