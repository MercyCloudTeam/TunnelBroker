<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\NodeTable;
use App\Admin\Repositories\IPPool;
use App\Admin\Repositories\Node;
use App\Jobs\CreateIPAllocation;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use IPTools\IP;
use IPTools\Network;

class IPPoolController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new IPPool(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('node_id');
            $grid->column('ip');
            $grid->column('cidr');
            $grid->column('allocation_size');
            $grid->column('subnet');
            $grid->column('ip_type');
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
        return Show::make($id, new IPPool(), function (Show $show) {
            $show->field('id');
            $show->field('node_id');
            $show->field('ip');
            $show->field('cidr');
            $show->field('allocation_size');
            $show->field('subnet');
            $show->field('ip_type');
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
        return Form::make(new IPPool(), function (Form $form) {
            $form->display('id');
            $form->selectTable('node_id')
                ->title('选择节点')
                ->dialogWidth('50%') // 弹窗宽度，默认 800px
                ->required()
                ->from(NodeTable::make(['id' => $form->getKey()])) // 设置渲染类实例，并传递自定义参数
                ->model(\App\Models\Node::class, 'id', 'title'); // 设置编辑数据显示
            $form->text('ip')->required();
            $form->text('cidr')->required()->default(48);
            $form->number('allocation_size')->min(1)->max(128)->default(64)->required();
            $form->select('ip_type')->options(['ipv4' => 'IPV4', 'ipv6' => 'IPV6'])->required()->default('ipv6');
            $form->switch('generated')->default(true);
            $form->text('type')->default('common')->required();

            $form->display('created_at');
            $form->display('updated_at');

            $form->saved(function (Form $form, $result) {
                $ip = new IP($form->ip);
                $ipType = strtolower($ip->version);
                if ($ipType != $form->ip_type) {
                    $form->response()->error('IP type mismatch');
                    return;
                } elseif ($ipType == 'ipv4' && $form->cidr > 32) {
                    $form->response()->error('IPv4 CIDR must be less than 32');
                    return;
                } elseif ($ipType == 'ipv6' && $form->cidr > 128) {
                    $form->response()->error('IPv6 CIDR must be less than 128');
                    return;
                }

                CreateIPAllocation::dispatch(\App\Models\IPPool::find($result));
            });
        });

    }
}
