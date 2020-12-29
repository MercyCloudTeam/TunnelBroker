<?php

namespace App\Admin\Repositories;

use App\Models\Tunnel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Tunnel extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
