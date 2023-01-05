<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Menu;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
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
    ]);
});
