<?php

namespace App\Admin\Repositories;

use App\Models\ASN as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ASN extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
