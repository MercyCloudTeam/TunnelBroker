<?php

namespace App\Http\Controllers\NodeComponent;

use App\Http\Controllers\Controller;

class NodeComponentBaseController extends Controller
{
    public static array $components = [
        'FRR',
        'WireGuard',
    ];

    public array $configure;


}
