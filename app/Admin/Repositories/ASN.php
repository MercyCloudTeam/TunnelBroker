<?php

namespace App\Admin\Repositories;

use App\Models\ASN as Model;
use Isifnet\PieAdmin\Repositories\EloquentRepository;

class ASN extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
