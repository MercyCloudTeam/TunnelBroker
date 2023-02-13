<?php

namespace App\Admin\Repositories;

use App\Models\Plan as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Plan extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
