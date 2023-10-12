<?php
/*
 * Copyright (c) 2023. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin;


use Encore\Admin\Widgets\Form;

/**
 * User: never615 <never615.com>
 * Date: 2023/10/9
 * Time: 19:34
 */
class RegisterForm extends Form
{

    public $title = '注册';


    public function handle(Request $request)
    {
        //dump($request->all());

        admin_success('Processed successfully.');

        //return back();
    }


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('name')->rules('required');
        $this->email('email')->rules('email');
        $this->mobile('mobile')->rules('required|mobile');
        $this->password('password', trans('admin.password'))->rules('required|confirmed');
        $this->password('password_confirmation', trans('admin.password_confirmation'))
            ->rules('required');
    }


    /**
     * The data of the form.
     *
     * @return  array $data
     */
    public function data()
    {
        //return [
        //    'name'       => 'John Doe',
        //    'email'      => 'John.Doe@gmail.com',
        //    'created_at' => now(),
        //];
    }
}