<?php
/**
 * Copyight (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Support\Arr;

class Button extends Field
{

    protected $class = 'btn-primary';


    public function info()
    {
        $this->class = 'btn-info';

        return $this;
    }


    public function on($event, $callback)
    {

        $js = $callback();

        $this->script = <<<EOT

        $('{$this->getElementClassSelector()}').on('$event', function() {
            $js
        });

EOT;
    }


    /**
     * Get element class string.
     *
     * @return mixed
     */
    public function getElementClassString()
    {
        $elementClass = $this->getElementClass();

        if (Arr::isAssoc($elementClass)) {
            $classes = [];

            foreach ($elementClass as $index => $class) {
                $classes[$index] = is_array($class) ? implode(' ', $class) : $class;
            }

            return $classes;
        }

        return implode(' ', $elementClass) . ' ' . $this->class;
    }
}
