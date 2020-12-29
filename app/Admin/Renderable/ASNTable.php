<?php

namespace App\Admin\Renderable;

use App\Models\ASN;
use App\Models\User;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Dcat\Admin\Models\Administrator;

class ASNTable extends LazyRenderable
{
    public function grid(): Grid
    {
        // 获取外部传递的参数
        $id = $this->id;

        return Grid::make(new ASN(), function (Grid $grid) {
            $grid->column('asn');
            $grid->paginate(10);
            $grid->disableActions();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('asn')->width(4);
            });
        });
    }
}
