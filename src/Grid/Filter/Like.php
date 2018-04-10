<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Filter;


class Like extends AbstractFilter
{
    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @param bool  $isDbQuery
     * @return array|mixed|void
     */
    public function condition($inputs,$isDbQuery=false)
    {
        $value = array_get($inputs, $this->column);

        if (is_array($value)) {
            $value = array_filter($value);
        }

        if (is_null($value) || empty($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, 'like', "%{$this->value}%",$isDbQuery);
    }
}
