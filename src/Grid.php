<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

use Closure;
use Encore\Admin\Exception\Handler;
use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Displayers\Actions;
use Encore\Admin\Grid\Displayers\RowSelector;
use Encore\Admin\Grid\Exporter;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Model;
use Encore\Admin\Grid\Row;
use Encore\Admin\Grid\Tools;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Eloquent\Model as MongodbModel;

class Grid extends \Encore\Admin\Grid
{
    /**
     * Options for grid.
     *
     * @var array
     */
    protected $options = [
        'usePagination'    => true,
        'useFilter'        => true,
        'useExporter'      => true,
        'useActions'       => true,
        'useRowSelector'   => true,
        'allowCreate'      => true,
        'allowBatchDelete' => true,
    ];


    /**
     * Add column to Grid.
     *
     * @param string $name
     * @param string $label
     *
     * @return Column
     */
    public function column($name, $label = '')
    {
        $relationName = $relationColumn = '';

        if (strpos($name, '.') !== false) {
            list($relationName, $relationColumn) = explode('.', $name);

            $relation = $this->model()->eloquent()->$relationName();

            $tempLabel = null;
            if (empty($label)) {
                $tempLabel = admin_translate($relationColumn);
            } else {
                $tempLabel = $label;
            }

//            $label = empty($label) ? ucfirst($relationColumn) : $label;

            $label = $tempLabel;
            $name = snake_case($relationName).'.'.$relationColumn;
        }

        $column = $this->addColumn($name, $label);

        if (isset($relation) && $relation instanceof Relation) {
            $this->model()->with($relationName);
            $column->setRelation($relationName, $relationColumn);
        }

        return $column;
    }

    /**
     * enable export.
     *
     * @author never615 add
     *
     * @return bool
     */
    public function enableExport()
    {
        return $this->option('useExporter', true);
    }

    /**
     * If allow batch delete.
     *
     * @return bool
     */
    public function allowBatchDeletion()
    {
        return $this->option('allowBatchDelete');
    }

    /**
     * Disable batch deletion.
     *
     * @return $this
     *
     * @deprecated
     */
    public function disableBatchDeletion()
    {
        return $this->option('allowBatchDelete', false);
    }


    /**
     * Dynamically add columns to the grid view.
     *
     * @param $method
     * @param $arguments
     *
     * @return Column
     */
    public function __call($method, $arguments)
    {

        if (isset($arguments[0])) {
            $label = $arguments[0];
        } else {
            $label = admin_translate($method);
        }

//        $label = isset($arguments[0]) ? $arguments[0] : ucfirst($method);

        if ($this->model()->eloquent() instanceof MongodbModel) {
            return $this->addColumn($method, $label);
        }

        if ($column = $this->handleGetMutatorColumn($method, $label)) {
            return $column;
        }

        if ($column = $this->handleRelationColumn($method, $label)) {
            return $column;
        }

        if ($column = $this->handleTableColumn($method, $label)) {
            return $column;
        }

        return $this->addColumn($method, $label);
    }

    /**
     * Get the string contents of the grid view.
     *
     * @return string
     */
    public function render()
    {
        try {
            $this->build();
        } catch (\Exception $e) {
            if (Input::get("_export_", null) != null) {
                Log::info($e);

                return;
            } else {
                return Handle::renderException($e);
            }
        }

        return view($this->view, $this->variables())->render();
    }


}
