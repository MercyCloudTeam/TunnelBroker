<?php

namespace App\Admin\Repositories;

use App\Models\User as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class User extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
