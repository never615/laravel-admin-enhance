<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form;

use Encore\Admin\Admin;
use Encore\Admin\Form;

//todo 增加了下列方法用于处理重复控件的问题,原库没有,待验证
class NestedForm extends Form\NestedForm
{

    /**
     * Fill data to all fields in form.
     *
     * @param array $data
     *
     * @return $this
     */
    public function fill(array $data, $field_id = 0)
    {
        $unique = 0;
        /* @var Field $field */
        foreach ($this->fields() as $field) {
            $field->addElementClass('hasMany-old-unique-' . $field_id . '-' . $unique);
            $unique++;
            $field->fill($data);
        }

        return $this;
    }


    /**
     * Get the html and script of template.
     *
     * @return array
     */
    public function getTemplateHtmlAndScript()
    {
        $html = '';
        $scripts = [];
        $unique = 0;

        /* @var Field $field */
        foreach ($this->fields() as $field) {

            $field->addElementClass('hasMany-new-unique-' . $unique);
            $unique++;

            //when field render, will push $script to Admin
            $html .= $field->render();

            /*
             * Get and remove the last script of Admin::$script stack.
             */
            if ($field->getScript()) {
                $scripts[] = array_pop(Admin::$script);
            }


        }

        return [ $html, implode("\r\n", $scripts) ];
    }

}
