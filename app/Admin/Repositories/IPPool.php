<?php

namespace App\Admin\Repositories;

use App\Models\IPPool as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class IPPool extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
