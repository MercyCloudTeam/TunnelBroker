<?php

namespace App\Admin\Renderable;

use App\Models\ASN;
use App\Models\Node;
use App\Models\User;
use Isifnet\PieAdmin\Grid;
use Isifnet\PieAdmin\Grid\LazyRenderable;
use Isifnet\PieAdmin\Models\Administrator;

class NodeTable extends LazyRenderable
{
    public function grid(): Grid
    {
        // 获取外部传递的参数
        $id = $this->id;

        return Grid::make(new Node(), function (Grid $grid) {
            $grid->column('ip');
            $grid->column('title');
            $grid->column('limit');

            $grid->paginate(10);
            $grid->disableActions();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('ip')->width(4);
            });
        });
    }
}
