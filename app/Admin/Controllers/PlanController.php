<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Plan;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->column('data');
            $grid->column('description');
            $grid->column('limit');
            $grid->column('bandwidth');
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
            $show->field('bandwidth');
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
            $form->text('limit');
            $form->text('bandwidth');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
