<?php

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form\Field;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class Select extends Field\Select
{
    private $sourceUrl;
    private $idField;

    protected $view = 'adminE::form.select';


    protected static $css = [
        '/vendor/laravel-admin/AdminLTE/plugins/select2/select2.min.css',
    ];

    protected static $js = [
        '/vendor/laravel-admin/AdminLTE/plugins/select2/select2.full.min.js',
    ];

    public function render()
    {
        if (empty($this->script)) {
            $this->script = <<<EOF
$("{$this->getElementClassSelector()}").select2({
    allowClear: true,
    placeholder: "{$this->label}"
});
EOF;
        }
//        else {
//            $this->initDefaultValue();
//        }


        if ($this->options instanceof \Closure) {
            if ($this->form) {
                $this->options = $this->options->bindTo($this->form->model());
            }

            $this->options(call_user_func($this->options, $this->value));
        }

        $this->options = array_filter($this->options);

        return parent::render()->with(['options' => $this->options]);
    }




    /**
     * Set options.
     *
     * @param array|callable|string $options
     *
     * @return $this|mixed
     */
    public function options($options = [])
    {
        // remote options
        if (is_string($options)) {
            return call_user_func_array([$this, 'loadOptionsFromRemote'], func_get_args());
        }

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        if (is_callable($options)) {
            $this->options = $options;
        } else {
            $this->options = (array) $options;
        }

        return $this;
    }

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
            $class = str_replace(['[', ']'], '_', $field);
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


    /**
     * Load options for other select on change.
     *
     * @param string $field ,子级
     * @param string $sourceUrl
     * @param string $idField
     * @param string $textField
     *
     * @return $this
     */
    public function load($field, $sourceUrl, $idField = 'id', $textField = 'text')
    {
        if (Str::contains($field, '.')) {
            $field = $this->formatName($field);
            $class = str_replace(['[', ']'], '_', $field);
        } else {
            $class = $field;
        }

        $script = <<<EOT
$(document).on('change', "{$this->getElementClassSelector()}", function () {
    var target = $(this).closest('.fields-group').find(".$class");
    $.get("$sourceUrl?q="+this.value, function (data) {
        target.find("option").remove();
        $(target).select2({
            data: $.map(data, function (d) {
                d.id = d.$idField;
                d.text = d.$textField;
                return d;
            }),
            allowClear: true,
            placeholder: "{$this->label}"
        }).trigger('change');
    });
});
EOT;

        Admin::script($script);

        return $this;
    }

    /**
     * Load options from remote.
     *
     * @param string $url
     * @param array  $parameters
     * @param array  $options
     *
     * @return $this
     */
    protected function loadOptionsFromRemote($url, $parameters = [], $options = [])
    {
        $ajaxOptions = [
            'url' => $url.'?'.http_build_query($parameters),
        ];

        $ajaxOptions = json_encode(array_merge($ajaxOptions, $options));

        $this->script = <<<EOT

$.ajax($ajaxOptions).done(function(data) {
  $("{$this->getElementClassSelector()}").select2({data: data});
});

EOT;

        return $this;
    }

    /**
     * Load options from ajax results.
     *
     * @param string $url
     * @param        $idField
     * @param        $textField
     *
     * @return $this
     */
    public function ajax($url, $idField = 'id', $textField = 'text')
    {

        $this->sourceUrl = $url;
        $this->idField = $idField;

        $this->script = <<<EOT
var current=$("{$this->getElementClassSelector()}");

$("{$this->getElementClassSelector()}").select2({
  ajax: {
    url: "$url",
    dataType: 'json',
    delay: 250,
    data: function (params) {
      return {
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

EOT;

        return $this;
    }
}
