<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Node;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->column('username');
            $grid->column('password');
            $grid->column('login_type');
            $grid->column('port');
            $grid->column('status');
            $grid->column('limit');
            $grid->column('bgp');
            $grid->column('config');
            $grid->column('asn');
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
        return Form::make(new Node(), function (Form $form) {
            $form->display('id');
            $form->text('title')->required();
            $form->ip('ip')->required();
            $form->ip('public_ip')->help('可为空，公开配置的IPV6，当节点处于NAT后使用');;
            $form->text('ip6')->required();
            $form->text('public_ip6')->help('可为空，公开配置的IPV6，当节点处于NAT后使用');

            $form->text('username')->default('root')->value($form->model()->username);;
            $form->select('login_type')->options(config('status.node.login_type'))->default('password')->value($form->model()->login_type);
            $form->password('password')->value($form->model()->password);

            $form->number('port')->max(65535)->min(1)->default(22)->value($form->model()->port);;
            $form->select('status')->options(config('status.node.status'))->default(1);
            $form->number('limit')->default(10000);
            $form->select('bgp')->options(config('status.node.bgp'))->placeholder('为空则不可添加BGP Tunnel');
            $form->keyValue('config')->value($form->model()->config);
            $form->number('asn')->min(1)->default(114514);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
