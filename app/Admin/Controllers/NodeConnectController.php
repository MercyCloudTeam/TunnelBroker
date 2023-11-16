<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\NodeConnect;
use Isifnet\PieAdmin\Form;
use Isifnet\PieAdmin\Grid;
use Isifnet\PieAdmin\Show;
use Isifnet\PieAdmin\Http\Controllers\AdminController;

class NodeConnectController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new NodeConnect(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('tunnel_id');
            $grid->column('right_node_id');
            $grid->column('left_node_id');
            $grid->column('cost');
            $grid->column('status');
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
        return Show::make($id, new NodeConnect(), function (Show $show) {
            $show->field('id');
            $show->field('tunnel_id');
            $show->field('right_node_id');
            $show->field('left_node_id');
            $show->field('cost');
            $show->field('status');
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
        return Form::make(new NodeConnect(), function (Form $form) {
            $form->display('id');
            $form->text('tunnel_id');
            $form->text('right_node_id');
            $form->text('left_node_id');
            $form->text('cost');
            $form->text('status');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
