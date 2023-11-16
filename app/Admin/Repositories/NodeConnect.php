<?php

namespace App\Admin\Repositories;

use App\Models\NodeConnect as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class NodeConnect extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
