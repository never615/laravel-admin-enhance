<?php

namespace Mallto\Admin\Widgets;

use Encore\Admin\Widgets\Widget;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Cookie;

class LanguageMenu extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'adminE::widgets.language-menu';

    /**
     * @return string
     */
    public function render()
    {
        $current = config('admin.multi-language.default');
        $cookie_name = config('admin.multi-language.cookie-name', 'locale');
        if(Cookie::has($cookie_name)) {
            $current = Cookie::get($cookie_name);
        }
        $languages = config("admin.multi-language.languages");

        return view($this->view, compact('languages', 'current'))->render();
    }
}
