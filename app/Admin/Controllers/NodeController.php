<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Node;
use App\Http\Controllers\NodeComponent\NodeComponentBaseController;
use Isifnet\PieAdmin\Form;
use Isifnet\PieAdmin\Grid;
use Isifnet\PieAdmin\Show;
use Isifnet\PieAdmin\Http\Controllers\AdminController;

class NodeController extends AdminController
{


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Node(), function (Grid $grid) {
//            $grid->hideColumns(['password']);
//            $grid->password(['password']);
//           dd( $grid->hideColumns(['password']));
//           dd( $grid->getVisibleColumnNames());
            $grid->column('id')->sortable();
            $grid->column('ip');
            $grid->column('country');
            $grid->column('ip6');
            $grid->column('port');
            $grid->column('status')->using(config('status.node.status'));
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
        return Show::make($id, new Node(), function (Show $show) {
            $show->field('id');
            $show->field('ip');
            $show->field('username');
            $show->field('password');
            $show->field('login_type');
            $show->field('port');
            $show->field('status');
            $show->field('limit');
            $show->field('bgp');
            $show->field('config');
            $show->field('asn');
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
        return Form::make(Node::with('components'), function (Form $form) {
            $form->column(6, function (Form $form) {

                $form->display('id');
                $form->text('title')->required();
                $form->ip('ip')->required();
                $form->ip('public_ip')->help('Can be empty, publicly configured IPV6, used when the node is behind NAT');;
                $form->text('ip6');
                $form->text('public_ip6')->help('Can be empty, publicly configured IPV6, used when the node is behind NAT');

                $form->text('username')->default('root')->value($form->model()->username);;
//                $form->select('login_type')->options(config('status.node.login_type'))->default('password')->value($form->model()->login_type);


                $form->radio('login_type')
                    ->when('password', function (Form $form) {
                        $form->password('password')->value($form->model()->password);
                    })
                    ->when('rsa', function (Form $form) {
                        $form->textarea('password');
                    })
                    ->options(config('status.node.login_type'))
                    ->default('password');

                $form->text('country');

                $form->number('port')->max(65535)->min(1)->default(22)->value($form->model()->port);;
                $form->select('status')->options(config('status.node.status'))->default(1);
                $form->number('limit')->default(10000);
                $form->keyValue('config')->value($form->model()->config);

                $form->display('created_at');
                $form->display('updated_at');
            });

            $form->column(6, function (Form $form) {

                $form->hasMany('components', function (Form\NestedForm $form) {
                    $form->select('component')->options(array_combine(NodeComponentBaseController::$components,NodeComponentBaseController::$components));
                    $form->select('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->default('active');
//                    $form->text('data')->help('JSON format');
                    $form->keyValue('data');
                });
            });
        });
    }
}
