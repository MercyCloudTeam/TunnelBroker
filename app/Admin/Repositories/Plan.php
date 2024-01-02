<?php

namespace App\Admin\Repositories;

use App\Models\Plan as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class Plan extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
