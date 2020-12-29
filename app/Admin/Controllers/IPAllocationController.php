<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\IPAllocation;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class IPAllocationController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new IPAllocation(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('node_id');
            $grid->column('tunnel_id');
            $grid->column('ip');
            $grid->column('cidr');
            $grid->column('type');
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
        return Show::make($id, new IPAllocation(), function (Show $show) {
            $show->field('id');
            $show->field('node_id');
            $show->field('tunnel_id');
            $show->field('ip');
            $show->field('cidr');
            $show->field('type');
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
        return Form::make(new IPAllocation(), function (Form $form) {
            $form->display('id');
            $form->text('node_id');
            $form->text('tunnel_id');
            $form->text('ip');
            $form->text('cidr');
            $form->text('type');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
