<?php
/**
 * Copyright (c) 2020. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;

use Encore\Admin\Form;

/**
 * User: never615 <never615.com>
 * Date: 2020/7/20
 * Time: 4:14 下午
 */
interface SubjectSettingExtendInterface
{

    public function extend(Form $form, $currentId);


    /**
     * @param Form $form
     *
     * @param      $adminUser
     *
     * @return mixed
     */
    public function formSaving(Form $form, $adminUser);


    /**
     * @param Form $form
     *
     * @return mixed
     */
    public function formSaved(Form $form);

}
