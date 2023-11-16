<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\Examples;
use App\Http\Controllers\Controller;
use Isifnet\PieAdmin\Http\Controllers\Dashboard;
use Isifnet\PieAdmin\Layout\Column;
use Isifnet\PieAdmin\Layout\Content;
use Isifnet\PieAdmin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('Dashboard')
            ->description('Description...')
            ->body(function (Row $row) {
              
            });
    }
}
