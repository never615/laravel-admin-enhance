<?php

namespace Mallto\Admin\Widgets;

use Encore\Admin\Widgets\Widget;
use Illuminate\Contracts\Support\Renderable;

class InfoBox extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'adminE::widgets.info-box';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * InfoBox constructor.
     *
     * @param string $name
     * @param string $icon
     * @param string $color
     * @param string $link
     * @param string $info
     * @param mixed  $isShowLink
     */
    public function __construct($name, $icon, $color, $link, $info, $isShowLink = true)
    {
        $this->data = [
            'name'       => $name,
            'icon'       => $icon,
            'link'       => $link,
            'info'       => $info,
            'isShowLink' => $isShowLink,
        ];

        $this->class("small-box bg-${color}");
    }

    /**
     * @return string
     */
    public function render()
    {
        $variables = array_merge($this->data, ['attributes' => $this->formatAttributes()]);

        return view($this->view, $variables)->render();
    }
}
