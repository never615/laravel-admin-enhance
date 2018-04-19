<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers;


use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\Route;
use Mallto\Admin\Controllers\Base\AdminCommonController;
use Mallto\Admin\Data\Menu;
use Mallto\Admin\Data\Role;

class MenuController extends AdminCommonController
{
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('admin.menu'));
            $content->description(trans('admin.list'));

            $content->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());

                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action(admin_url('auth/menus'));

                    $form->select('parent_id', trans('admin.parent_id'))->options(Menu::selectOptions());
                    $form->text('title', trans('admin.title'))->rules('required');
                    $form->icon('icon',
                        trans('admin.icon'))->default('fa-bars')->rules('required')->help($this->iconHelp());
                    $form->text('uri', trans('admin.uri'));
                    if (!config("admin.auto_menu")) {
                        $form->multipleSelect('roles', trans('admin.roles'))->options(Role::all()->pluck('name', 'id'));
                    }
                    $form->hidden('_token')->default(csrf_token());
                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
                });
            });
        });
    }

    /**
     * Redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('menu.edit', ['id' => $id]);
    }

    /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        return Menu::tree(function (Tree $tree) {
            $tree->disableCreate();

            $tree->branch(function ($branch) {
                $payload = "<i class='fa {$branch['icon']}'></i>&nbsp;<strong>{$branch['title']}</strong>";

                if (!isset($branch['children']) && $branch['uri']) {

                    if (Route::has($branch['uri'])) {
                        $uri = route($branch['uri']);
                    } else {
                        if (url()->isValidUrl($branch['uri'])) {
                            $uri = $branch['uri'];
                        } else {
                            $uri = admin_base_path($branch['uri']);
                        }
                    }
                    $payload .= "&nbsp;&nbsp;&nbsp;<a href=\"$uri\" class=\"dd-nodrag\">$uri</a>";
                }

                return $payload;
            });
        });
    }


    /**
     * Help message for icon field.
     *
     * @return string
     */
    protected function iconHelp()
    {
        return 'For more icons please see <a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a>';
    }


    /**
     * 获取这个模块的标题
     *
     * @return mixed
     */
    protected function getHeaderTitle()
    {
        return "菜单管理";
    }

    /**
     * 获取这个模块的Model
     *
     * @return mixed
     */
    protected function getModel()
    {
        return Menu::class;
    }

    protected function gridOption(Grid $grid)
    {
    }

    protected function formOption(Form $form)
    {
        $form->select('parent_id', trans('admin.parent_id'))->options(Menu::selectOptions());
        $form->text('title', trans('admin.title'))->rules('required');
        $form->icon('icon',
            trans('admin.icon'))->default('fa-bars')->rules('required')->help($this->iconHelp());
        $form->text('uri', trans('admin.uri'));
        if (!config("admin.auto_menu")) {
            $form->multipleSelect('roles', trans('admin.roles'))
                ->options(Role::all()->pluck('name', 'id'));
        }
    }


    public function destroy($id)
    {
        $this->currentId = $id;

        try {
            if ($this->form()->destroy($id)) {
                //同时删除所有的子菜单
                Menu::where("parent_id", $id)->delete();

                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.delete_succeeded'),
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => trans('admin.delete_failed'),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status'  => false,
                'message' => "为了数据安全,暂时无法删除存在关联数据的数据",
            ]);
        }
    }
}
