<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Choice
 *
 *
 * 保存到数据库和读取的数据需要时json字符串,如:
 * [ { "id": "11025", "text": "小柯", "type": "users" }, { "id": "4", "text": "白金卡", "type": "member_levels" } ]
 *
 * @package Mallto\Admin\Form\Field
 */
class Choice extends Field
{

    protected $view = 'adminE::form.choice';

    protected static $css = [
        '/vendor/laravel-adminE/diy/choice/stylesheets/choice3.css',
        '/vendor/laravel-adminE/diy/choice/stylesheets/fx_pc_all.css',
    ];

    protected static $js = [
        '/vendor/laravel-adminE/diy/choice/scripts/main.js',
    ];

    protected $selects;

    protected $dataUrls;


    /**
     * Set options.
     *
     * @param array|callable|string $options
     *
     * @return $this|mixed
     */
    public function options($options = [])
    {

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


    /**
     * Set options.
     *
     * @param array|callable $selects
     *
     * @return $this|mixed
     */
    public function selects($selects = [])
    {
        if ($selects instanceof Arrayable) {
            $selects = $selects->toArray();
        }

        if (is_callable($selects)) {
            $this->selects = $selects;
        } else {
            $this->selects = (array) $selects;
        }

        return $this;
    }


    public function dataUrls($dataUrls = [])
    {
        $this->dataUrls = $dataUrls;

        return $this;
    }


    public function render()
    {
        $this->script = <<<EOT



EOT;
        if ($this->options instanceof \Closure) {
            if ($this->form) {
                $this->options = $this->options->bindTo($this->form->model());
            }

            $this->options(call_user_func($this->options, $this->value));
        }

        $this->options = array_filter($this->options);

        return parent::fieldRender([
            'options'  => $this->options,
            'selects'  => $this->selects,
            'dataUrls' => $this->dataUrls,
        ]);

    }

}
