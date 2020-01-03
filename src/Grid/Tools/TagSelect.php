<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Created by PhpStorm.
 * User: never615 <never615.com>
 * Date: 11/08/2017
 * Time: 11:52 AM
 */
class TagSelect extends AbstractTool
{

    protected $view = 'adminE::grid.tag_select';

    protected static $css = [
        '/vendor/laravel-admin/AdminLTE/plugins/select2/select2.min.css',
    ];

    protected static $js = [
        '/vendor/laravel-admin/AdminLTE/plugins/select2/select2.full.min.js',
    ];

    protected $options = [];

    protected $url;

    /**
     * @var
     */
    private $placeholder;


    /**
     * TagSelect constructor.
     *
     * @param        $options
     * @param        $actionUrl ,店铺标签按钮提交的地址
     * @param string $placeholder
     */
    public function __construct($options, $actionUrl, $placeholder = "设置标签")
    {
        $this->placeholder = $placeholder;
        $this->options = $options;
        $this->url = $actionUrl;
    }


    protected function script()
    {

    }


    public function url($url)
    {
        $this->url = $url;

        return $this;
    }


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
     * {@inheritdoc}
     */
    public function render()
    {
        return view($this->view, [
            "options"     => $this->options,
            "url"         => $this->url,
            'placeholder' => $this->placeholder,
        ]);
    }
}
