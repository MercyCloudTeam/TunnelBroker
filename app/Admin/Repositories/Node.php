<?php

namespace App\Admin\Repositories;

use App\Models\Node as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class Node extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
