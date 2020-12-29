<?php

namespace App\Admin\Repositories;

use App\Models\Node as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Node extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
