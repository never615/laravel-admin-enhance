<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

trait ModelTree
{

    /**
     * @var array
     */
    protected static $branchOrder = [];

    /**
     * @var string
     */
    protected $parentColumn = 'parent_id';

    /**
     * @var string
     */
    protected $titleColumn = 'title';

    /**
     * @var string
     */
    protected $orderColumn = 'order';

    /**
     * @var \Closure
     */
    protected $queryCallback;


    /**
     * Get children of current node.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, $this->parentColumn);
    }


    /**
     * Get parent of current node.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, $this->parentColumn);
    }


    /**
     * @return string
     */
    public function getParentColumn()
    {
        return $this->parentColumn;
    }


    /**
     * Set parent column.
     *
     * @param string $column
     */
    public function setParentColumn($column)
    {
        $this->parentColumn = $column;
    }


    /**
     * Get title column.
     *
     * @return string
     */
    public function getTitleColumn()
    {
        return $this->titleColumn;
    }


    /**
     * Set title column.
     *
     * @param string $column
     */
    public function setTitleColumn($column)
    {
        $this->titleColumn = $column;
    }


    /**
     * Get order column name.
     *
     * @return string
     */
    public function getOrderColumn()
    {
        return $this->orderColumn;
    }


    /**
     * Set order column.
     *
     * @param string $column
     */
    public function setOrderColumn($column)
    {
        $this->orderColumn = $column;
    }


    /**
     * Set query callback to model.
     *
     * @param \Closure|null $query
     *
     * @return $this
     */
    public function withQuery(\Closure $query = null)
    {
        $this->queryCallback = $query;

        return $this;
    }


    /**
     * Format data to tree like array.
     *
     * @return array
     */
    public function toTree()
    {
        return $this->buildNestedArray();
    }


    /**
     * Build Nested array.
     *
     * @param array $nodes
     * @param int $parentId
     *
     * @return array
     */
    protected function buildNestedArray(array $nodes = [], $parentId = 0)
    {
        $branch = [];

        if (empty($nodes)) {
            $nodes = $this->allNodes();
        }

        foreach ($nodes as $node) {
//            if (!$node) {
//                continue;
//            }
            if ($node[$this->parentColumn] == $parentId) {
                $children = $this->buildNestedArray($nodes, $node[$this->getKeyName()]);

                if ($children) {
                    $node['children'] = $children;
                }

                $branch[] = $node;
            }
        }

        return $branch;
    }


    /**
     * Get all elements.
     *
     * @return mixed
     */
    public function allNodes()
    {
        $orderColumn = DB::getQueryGrammar()->wrap($this->orderColumn);
        $byOrder = $orderColumn . ' = 0,' . $orderColumn;

        $self = new static();

        if ($this->queryCallback instanceof \Closure) {
            $self = call_user_func($this->queryCallback, $self);
        }

        return $self->orderByRaw($byOrder)->get()->toArray();
    }


    /**
     * Set the order of branches in the tree.
     *
     * @param array $order
     *
     * @return void
     */
    protected static function setBranchOrder(array $order)
    {
        static::$branchOrder = array_flip(array_flatten($order));

        static::$branchOrder = array_map(function ($item) {
            return ++$item;
        }, static::$branchOrder);
    }


    /**
     * Save tree order from a tree like array.
     *
     * @param array $tree
     * @param int $parentId
     */
    public static function saveOrder($tree = [], $parentId = 0)
    {
        if (empty(static::$branchOrder)) {
            static::setBranchOrder($tree);
        }

        foreach ($tree as $branch) {
            $node = static::find($branch['id']);

            $node->{$node->getParentColumn()} = $parentId;
            $node->{$node->getOrderColumn()} = static::$branchOrder[$branch['id']];
            $node->save();

            if (isset($branch['children'])) {
                static::saveOrder($branch['children'], $branch['id']);
            }
        }
    }


    /**
     * Get options for Select field in form.
     *
     * @param array $nodes
     * @param bool $root ,是否返回root节点
     * @param bool $defaultBlack ,是否使用默认的空格大小
     * @param int|array $parentId ,进来的nodes默认只有parent_id是0的才能进行下一步,此配置支持接收数组,可以配置多个parentId
     *
     * @return \Illuminate\Support\Collection
     */
    public static function selectOptions(
        array $nodes = null,
              $root = true,
              $defaultBlack = true,
              $parentId = 0
    )
    {
        $options = (new static())->buildSelectOptions($nodes, $parentId, "", $defaultBlack);

        if ($root) {
            return collect($options)->prepend('无上级节点', $parentId)->all();
        } else {
            return collect($options)->all();
        }
    }


    /**
     * Build options of select field in form.
     *
     * @param array $nodes
     * @param int $parentId
     * @param string $prefix
     *
     * @param bool $defaultBlack
     *
     * @return array
     */
    protected function buildSelectOptions(
        array $nodes = null,
              $parentId = 0,
              $prefix = '',
              $defaultBlack = true
    )
    {
        if ($defaultBlack) {
            $prefix = $prefix ?: str_repeat('&nbsp;', 6);
        } else {
            $prefix = $prefix ?: str_repeat('&nbsp;', 2);
        }

        $options = [];

        if ($nodes === null) {
            $nodes = $this->allNodes();
        }

        $parentId = (array)$parentId;

        foreach ($nodes as $node) {
            if ($defaultBlack) {
                $node[$this->titleColumn] = $prefix . '&nbsp;' . $node[$this->titleColumn];
            }

//            if ($node[$this->parentColumn] == $parentId) {
            if (in_array($node[$this->parentColumn], $parentId)) {
                $children = $this->buildSelectOptions($nodes, $node[$this->getKeyName()], $prefix . $prefix);

                $options[$node[$this->getKeyName()]] = $node[$this->titleColumn];

                if ($children) {
                    $options += $children;
                }
            }
        }

        return $options;
    }


    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->where($this->parentColumn, $this->getKey())->delete();

        return parent::delete();
    }


    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Model $branch) {
            $parentColumn = $branch->getParentColumn();

            if (Request::has($parentColumn) && Request::input($parentColumn) == $branch->getKey()) {
                throw new \Exception(trans('admin.parent_select_error'));
            }

            if (Request::has('_order')) {
                $order = Request::input('_order');

                Request::offsetUnset('_order');

                static::tree()->saveOrder($order);

                return false;
            }

            return $branch;
        });
    }
}
