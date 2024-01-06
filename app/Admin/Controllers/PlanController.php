<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Plan;
use Isifnet\PieAdmin\Form;
use Isifnet\PieAdmin\Grid;
use Isifnet\PieAdmin\Show;
use Isifnet\PieAdmin\Http\Controllers\AdminController;

class PlanController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Plan(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('slug');
            $grid->column('description');
            $grid->column('limit');
            $grid->column('ipv6_num');
            $grid->column('ipv4_num');
            $grid->column('speed');
            $grid->column('traffic');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Plan(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('slug');
            $show->field('data');
            $show->field('description');
            $show->field('limit');
            $show->field('ipv6_num');
            $show->field('ipv4_num');
            $show->field('speed');
            $show->field('traffic');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Plan(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('slug');
            $form->text('data');
            $form->text('description');
            $form->number('limit');
            $form->number('ipv6_num');
            $form->number('ipv4_num');
            $form->number('speed')->help('Mbps');
            $form->number('traffic')->help('GB');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
