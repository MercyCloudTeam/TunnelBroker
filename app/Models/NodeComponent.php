<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodeComponent extends Model
{
    use HasFactory;

    protected $table = 'nodes_components';

    protected $fillable = [
        'node_id',
        'component',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
