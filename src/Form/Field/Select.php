<?php

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Support\Str;

class Select extends Field\Select
{

    private $sourceUrl;

    private $idField;

    protected $view = 'adminE::form.select';

//    private function initDefaultValue()
//    {
//        //只支持ajaxLoad自动设置默认值目前
//        if (!is_null($this->sourceUrl)) {
//            $tempValue = $this->value;
//            if (is_array($tempValue)) {
//                $tempValue = implode(",", $tempValue);
//            }
//
//            $this->script .= <<<EOT
//if(typeof target != "undefined"){
//var fatherValue=target.val();
//$.get("{$this->sourceUrl}?{$this->idField}={$tempValue}&father_value="+fatherValue, function (data) {
//    for(item of data){
//        current.append("<option value='"+item.id+"' selected>"+(item.text?item.text:"")+"</option>");
//    }
//});
//}
//
//
//EOT;
//
//        }
//    }

    /**
     * Load options for other select on change from ajax results.
     *
     * 和load的原理类似,只不过支持ajax分页请求数据,
     * 然后支持自动加载默认值
     *
     * @param string $field ,父级
     * @param string $sourceUrl
     * @param string $idField
     * @param string $textField
     *
     * @return $this
     */
    public function ajaxLoad($field, $sourceUrl, $idField = 'id', $textField = 'text')
    {
        if (Str::contains($field, '.')) {
            $field = $this->formatName($field);
            $class = str_replace([ '[', ']' ], '_', $field);
        } else {
            $class = $field;
        }

        $this->sourceUrl = $sourceUrl;
        $this->idField = $idField;

        $this->script = <<<EOT
        
                
var current=$("{$this->getElementClassSelector()}");
var target = current.closest('.fields-group').find(".$class");

var init=function (){
    current.select2({
        ajax: {
          url: "$sourceUrl",
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              father_value:target.val(),
              q: params.term,
              page: params.page
            };
          },
          processResults: function (data, params) {
            params.page = params.page || 1;
            return {
              results: $.map(data.data, function (d) {
                         d.id = d.$idField;
                         d.text = d.$textField;
                         return d;
                      }),
              pagination: {
                more: data.next_page_url
              }
            };
          },
          cache: true
        },
        allowClear: true,
        placeholder: "{$this->label}",
        minimumInputLength: 1,
        escapeMarkup: function (markup) {
            return markup;
        }
    });
}

init();

  
$(document).on('change', "{$this->getElementClassSelector()}", function () {
   init();
});


EOT;

        return $this;
    }

}
