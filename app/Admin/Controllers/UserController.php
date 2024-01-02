<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use Isifnet\PieAdmin\Form;
use Isifnet\PieAdmin\Grid;
use Isifnet\PieAdmin\Show;
use Isifnet\PieAdmin\Http\Controllers\AdminController;

class UserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('limit');
            $grid->column('email');
            $grid->column('email_verified_at');
//            $grid->column('password');
//            $grid->column('two_factor_secret');
//            $grid->column('two_factor_recovery_codes');
            $grid->column('remember_token');
//            $grid->column('current_team_id');
            $grid->avatar('profile_photo_path');
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
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('limit');
            $show->field('email');
            $show->field('email_verified_at');
            $show->field('password');
            $show->field('two_factor_secret');
            $show->field('two_factor_recovery_codes');
            $show->field('remember_token');
            $show->field('current_team_id');
            $show->field('profile_photo_path');
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
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('limit');
            $form->text('email');
            $form->datetime('email_verified_at');
//            $form->text('password');
//            $form->text('two_factor_secret');
//            $form->text('two_factor_recovery_codes');
//            $form->text('remember_token');
//            $form->text('current_team_id');
//            $form->text('profile_photo_path');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
