<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Grid\Filter;

use Encore\Admin\Grid\Filter\AbstractFilter;

class Where extends AbstractFilter
{
    /**
     * Query closure.
     *
     * @var \Closure
     */
    protected $where;

    /**
     * Input value from presenter.
     *
     * @var
     */
    public $input;

    /**
     * Where constructor.
     *
     * @param \Closure $query
     * @param string   $label
     * @param string   $column
     */
    public function __construct(\Closure $query, $label, $column = null)
    {
        $this->where = $query;

        $this->label = $this->formatLabel($label);
        $this->column = $column ?: static::getQueryHash($query, $this->label);
        $this->id = $this->formatId($this->column);

        $this->setupDefaultPresenter();
    }

    /**
     * Get the hash string of query closure.
     *
     * @param \Closure $closure
     * @param string   $label
     *
     * @return string
     */
    public static function getQueryHash(\Closure $closure, $label = '')
    {
        $reflection = new \ReflectionFunction($closure);

        return md5($reflection->getFileName().$reflection->getStartLine().$reflection->getEndLine().$label);
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|mixed|void
     */
    public function condition($inputs, $isDbQuery = false)
    {
        $value = array_get($inputs, $this->column ?: static::getQueryHash($this->where, $this->label));

        if (is_array($value)) {
            $value = array_filter($value);
        }

        if (is_null($value) || empty($value)) {
            //todo 用户卡券导出有问题,暂时特别处理一下
            $value = array_get($inputs, 'aed52fa5b597286d24d5c2bc26b9a0de');
            if (is_null($value) || empty($value)) {
                return;
            }
        }

        $this->input = $this->value = $value;

        return $this->buildCondition($this->where->bindTo($this), $isDbQuery);
    }
}
