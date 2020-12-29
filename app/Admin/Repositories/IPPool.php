<?php

namespace App\Admin\Repositories;

use App\Models\IPPool as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class IPPool extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
