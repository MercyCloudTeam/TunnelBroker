<?php

namespace App\Admin\Repositories;

use App\Models\Tunnel as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class Tunnel extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
