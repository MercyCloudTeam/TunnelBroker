<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\ASNTable;
use App\Admin\Renderable\NodeTable;
use App\Admin\Renderable\UserTable;
use App\Admin\Repositories\Tunnel;
use App\Models\ASN;
use App\Models\Node;
use App\Models\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TunnelController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Tunnel(['node']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('mode');
            $grid->column('local');
            $grid->column('remote');
            $grid->column('ip4')->display(function ($ip4) {
                return $ip4.$this->ip4_cidr;
            });
            $grid->column('ip6')->display(function ($ip4) {
                return $ip4.$this->ip6_cidr;
            });;
            $grid->column('status');
            $grid->column('node.title');
            $grid->column('interface');
            $grid->column('srcport');
            $grid->column('dstport');
//            $grid->column('in')->display(function ($v){
//                return \App\Http\Controllers\TunnelController::hbw($v);
//            });
//            $grid->column('out')->display(function ($v){
//                return \App\Http\Controllers\TunnelController::hbw($v);
//            });
//            $grid->column('config');
//            $grid->column('user_id');
//            $grid->column('node_id');
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
        return Show::make($id, new Tunnel(), function (Show $show) {
            $show->field('id');
            $show->field('mode');
            $show->field('local');
            $show->field('remote');
            $show->field('ip4');
            $show->field('ip4_cidr');
            $show->field('ip6');
            $show->field('ip6_cidr');
            $show->field('key');
            $show->field('ttl');
            $show->field('vlan');
            $show->field('status');
            $show->field('mac');
            $show->field('interface');
            $show->field('srcport');
            $show->field('dstport');
            $show->field('in');
            $show->field('out');
            $show->field('config');
            $show->field('user_id');
            $show->field('node_id');
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
        return Form::make(new Tunnel(), function (Form $form) {
            $form->display('id');
            $form->select('mode')->options(config('status.tunnel.type'))->default('sit');
            $form->text('local')->help('默认使用节点IP，当需要使用本地指定IP的时候配置');
            $form->text('remote')->required();
            $form->select('status')->options(config('status.tunnel.status'))->default(2)->required();


            $form->ip('ip4');
            $form->number('ip4_cidr')->default(30)->max(32)->min(1);
            $form->text('ip6');
            $form->number('ip6_cidr')->default(64)->max(128)->min(1);
            $form->password('key');
            $form->number('ttl')->default(255);
            $form->number('vlan');
            $form->text('mac');
            $form->text('interface');
            $form->number('srcport')->min(0)->max(65535);
            $form->number('dstport')->min(0)->max(65535);
            $form->display('in');
            $form->display('out');
            $form->text('config');
//            $form->selectTable('');
            $form->selectTable('user_id')
                ->title('选择用户')
                ->required()
                ->dialogWidth('50%') // 弹窗宽度，默认 800px
                ->from(UserTable::make(['id' => $form->getKey()])) // 设置渲染类实例，并传递自定义参数
                ->model(User::class, 'id', 'username'); // 设置编辑数据显示
            $form->selectTable('node_id')
                ->title('选择节点')
                ->dialogWidth('50%') // 弹窗宽度，默认 800px
                ->required()
                ->from(NodeTable::make(['id' => $form->getKey()])) // 设置渲染类实例，并传递自定义参数
                ->model(Node::class, 'id', 'title'); // 设置编辑数据显示
            $form->selectTable('asn_id')
                ->title('选择ASN')
                ->dialogWidth('50%') // 弹窗宽度，默认 800px
                ->from(ASNTable::make(['id' => $form->getKey()])) // 设置渲染类实例，并传递自定义参数
                ->model(ASN::class, 'id', 'asn')
            ->help('仅BGP Tunnel时需配置'); // 设置编辑数据显示


            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
