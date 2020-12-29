<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ASN;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ASNController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ASN(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('limit');
            $grid->column('validate');
            $grid->column('asn');
            $grid->column('loa');
            $grid->column('email_verified_at');
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
        return Show::make($id, new ASN(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('limit');
            $show->field('validate');
            $show->field('asn');
            $show->field('loa');
            $show->field('email_verified_at');
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
        return Form::make(new ASN(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('limit');
            $form->switch('validate');
            $form->text('asn');
            $form->file('loa');
            $form->datetime('email_verified_at');
            $form->email('email');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
