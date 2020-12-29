<?php

namespace App\Admin\Repositories;

use App\Models\IPAllocation as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class IPAllocation extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
