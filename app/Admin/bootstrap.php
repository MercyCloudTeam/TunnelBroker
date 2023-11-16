<?php

use Isifnet\PieAdmin\Admin;
use Isifnet\PieAdmin\Grid;
use Isifnet\PieAdmin\Form;
use Isifnet\PieAdmin\Grid\Filter;
use Isifnet\PieAdmin\Show;
use Isifnet\PieAdmin\Layout\Menu;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Isifnet\PieAdmin\Form::extend('php', PHPEditor::class);
 * Isifnet\PieAdmin\Grid\Column::extend('php', PHPEditor::class);
 * Isifnet\PieAdmin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Admin::menu(function (Menu $menu) {
    $menu->add([
        [
            'id'            => 100,
            'title'         => 'Tunnels',
            'icon'          => 'fa-chain',
            'uri'           => '/tunnels',
            'parent_id'     => 0,
        ],
        [
            'id'            => 200,
            'title'         => 'IP Manager',
            'icon'          => 'fa-university',
            'uri'           => '',
            'parent_id'     => 0,
        ],
        [
            'id'            => 201,
            'title'         => 'IP Pool',
            'icon'          => 'fa-list-alt',
            'uri'           => '/ip/pool',
            'parent_id'     => 200,
        ],
        [
            'id'            => 202,
            'title'         => 'IP Allocation',
            'icon'          => 'fa-list-ul',
            'uri'           => '/ip/allocation',
            'parent_id'     => 200,
        ],
        [
            'id'            => 300,
            'title'         => 'Nodes',
            'icon'          => 'fa-server',
            'uri'           => '/nodes',
            'parent_id'     => 0,
        ],
        [
            'id'            => 301,
            'title'         => 'Nodes',
            'icon'          => 'fa-server',
            'uri'           => '/nodes',
            'parent_id'     => 300,
        ],
        [
            'id'            => 302,
            'title'         => 'Connect',
            'icon'          => 'fa-server',
            'uri'           => '/node-connect',
            'parent_id'     => 300,
        ],
        [
            'id'            => 400,
            'title'         => 'ASN',
            'icon'          => 'fa-list-ol',
            'uri'           => '/asn',
            'parent_id'     => 0,
        ],
        [
            'id'            => 500,
            'title'         => 'User',
            'icon'          => 'fa-user',
            'uri'           => '/user',
            'parent_id'     => 0,
        ],
        [
            'id'           => 600,
            'title'         => 'BGP',
            'icon'          => 'fa-bug',
            'uri'           => '/bgp/filter',
            'parent_id'     => 0,
        ],
        [
            'id'           => 601,
            'title'         => 'BGP Filter',
            'icon'          => 'fa-fire',
            'uri'           => '/bgp/filter',
            'parent_id'     => 600,
        ],
        [
            'id'            => 700,
            'title'         => 'Settings',
            'icon'          => 'fa-cog',
            'uri'           => '/settings',
            'parent_id'     => 0,
        ],
    ]);
});
