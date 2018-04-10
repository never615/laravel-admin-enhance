<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Malto\Admin\Grid;

use Closure;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mallto\Tool\Utils\AppUtils;

class Column extends Grid\Column
{
    /**
     * Format label.
     *
     * @param $label
     *
     * @return mixed
     */
    protected function formatLabel($label)
    {
        $tempLabel = null;

        if (!empty($label)) {
            $tempLabel = $label;
        } else {
            $tempLabel = admin_translate($this->name);
        }

        return str_replace(['.', '_'], ' ', $tempLabel);
    }


    /**
     * Create the column sorter.
     *
     * @return string|void
     */
    public function sorter()
    {
        if (!$this->sortable) {
            return;
        }

        $icon = 'fa-sort';
        $type = 'desc';

        if ($this->isSorted()) {
            $type = $this->sort['type'] == 'desc' ? 'asc' : 'desc';
            $icon .= "-amount-{$this->sort['type']}";
        }

        $query = app('request')->all();
        $query = array_merge($query,
            [$this->grid->model()->getSortName() => ['column' => $this->name, 'type' => $type]]);

        $url = URL::current().'?'.http_build_query($query);
        $url=AppUtils::checkHttpProtocol($url);

        return "<a class=\"fa fa-fw $icon\" href=\"$url\"></a>";
    }
}
