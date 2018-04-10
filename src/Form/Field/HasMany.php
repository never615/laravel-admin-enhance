<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;


use Encore\Admin\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\NestedForm;
use Illuminate\Database\Eloquent\Relations\HasMany as Relation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class HasMany.
 */
class HasMany extends Field\HasMany
{
    /**
     * Build Nested form for related data.
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function buildRelatedForms()
    {
        if (is_null($this->form)) {
            return [];
        }

        $model = $this->form->model();

        $relation = call_user_func([$model, $this->relationName]);

        if (!$relation instanceof Relation && !$relation instanceof MorphMany) {
            throw new \Exception('hasMany field must be a HasMany or MorphMany relation.');
        }

        $forms = [];

        /*
         * If redirect from `exception` or `validation error` page.
         *
         * Then get form data from session flash.
         *
         * Else get data from database.
         */
        $field_id = 0;
        if ($values = old($this->column)) {
            foreach ($values as $key => $data) {
                if ($data[NestedForm::REMOVE_FLAG_NAME] == 1) {
                    continue;
                }

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)
                    ->fill($data, $field_id);
                $field_id++;
            }
        } else {
            foreach ($this->value as $data) {
                $key = array_get($data, $relation->getRelated()->getKeyName());

                $forms[$key] = $this->buildNestedForm($this->column, $this->builder, $key)
                    ->fill($data, $field_id);
                $field_id++;
            }
        }

        return $forms;
    }
}
