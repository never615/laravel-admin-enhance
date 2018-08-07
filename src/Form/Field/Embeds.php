<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Form\Field;


use Encore\Admin\Form;
use Encore\Admin\Form\EmbeddedForm;
use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Embeds extends Field\Embeds
{
    protected $view = 'adminE::form.embeds';

}
