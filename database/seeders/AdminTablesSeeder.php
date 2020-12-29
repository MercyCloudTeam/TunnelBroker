<?php

namespace Database\Seeders;

use Dcat\Admin\Models;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Models\Menu;
use Dcat\Admin\Models\Role;
use Illuminate\Database\Seeder;
use DB;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createdAt = date('Y-m-d H:i:s');

        // base tables
        Models\Menu::truncate();
        Models\Menu::insert(
            [
                [
                    'parent_id'     => 0,
                    'order'         => 1,
                    'title'         => 'Index',
                    'icon'          => 'feather icon-bar-chart-2',
                    'uri'           => '/',
                    'created_at'    => $createdAt,
                ],
                [
                    'parent_id'     => 0,
                    'order'         => 2,
                    'title'         => 'Admin',
                    'icon'          => 'feather icon-settings',
                    'uri'           => '',
                    'created_at'    => $createdAt,
                ],
                [
                    'parent_id'     => 2,
                    'order'         => 3,
                    'title'         => 'Users',
                    'icon'          => '',
                    'uri'           => 'auth/users',
                    'created_at'    => $createdAt,
                ],
                [
                    'parent_id'     => 2,
                    'order'         => 4,
                    'title'         => 'Roles',
                    'icon'          => '',
                    'uri'           => 'auth/roles',
                    'created_at'    => $createdAt,
                ],
                [
                    'parent_id'     => 2,
                    'order'         => 5,
                    'title'         => 'Permission',
                    'icon'          => '',
                    'uri'           => 'auth/permissions',
                    'created_at'    => $createdAt,
                ],
                [
                    'parent_id'     => 2,
                    'order'         => 6,
                    'title'         => 'Menu',
                    'icon'          => '',
                    'uri'           => 'auth/menu',
                    'created_at'    => $createdAt,
                ],
                [
                    'parent_id'     => 2,
                    'order'         => 7,
                    'title'         => 'Extensions',
                    'icon'          => '',
                    'uri'           => 'auth/extensions',
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 8,
                    "title" => "tunnel",
                    "icon" => "fa-cloud",
                    "uri" => "/tunnels",
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 9,
                    "title" => "node",
                    "icon" => "fa-server",
                    "uri" => "/nodes",
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 10,
                    "title" => "ippool",
                    "icon" => "fa-connectdevelop",
                    "uri" => "/ip/pool",
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 11,
                    "title" => "ipallocation",
                    "icon" => "fa-globe",
                    "uri" => "/ip/allocation",
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 12,
                    "title" => "asn",
                    "icon" => "fa-jsfiddle",
                    "uri" => "/asn",
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 13,
                    "title" => "bgp_filter",
                    "icon" => "fa-fire",
                    "uri" => "/bgp/filter",
                    'created_at'    => $createdAt,
                ],
                [
                    "parent_id" => 0,
                    "order" => 14,
                    "title" => "user",
                    "icon" => "fa-user",
                    "uri" => "/user",
                    'created_at'    => $createdAt,
                ]
            ]
        );

        Models\Permission::truncate();
        Models\Permission::insert(
            [
                [
                    'id'          => 1,
                    'name'        => 'Auth management',
                    'slug'        => 'auth-management',
                    'http_method' => '',
                    'http_path'   => '',
                    'parent_id'   => 0,
                    'order'       => 1,
                    'created_at'  => $createdAt,
                ],
                [
                    'id'          => 2,
                    'name'        => 'Users',
                    'slug'        => 'users',
                    'http_method' => '',
                    'http_path'   => '/auth/users*',
                    'parent_id'   => 1,
                    'order'       => 2,
                    'created_at'  => $createdAt,
                ],
                [
                    'id'          => 3,
                    'name'        => 'Roles',
                    'slug'        => 'roles',
                    'http_method' => '',
                    'http_path'   => '/auth/roles*',
                    'parent_id'   => 1,
                    'order'       => 3,
                    'created_at'  => $createdAt,
                ],
                [
                    'id'          => 4,
                    'name'        => 'Permissions',
                    'slug'        => 'permissions',
                    'http_method' => '',
                    'http_path'   => '/auth/permissions*',
                    'parent_id'   => 1,
                    'order'       => 4,
                    'created_at'  => $createdAt,
                ],
                [
                    'id'          => 5,
                    'name'        => 'Menu',
                    'slug'        => 'menu',
                    'http_method' => '',
                    'http_path'   => '/auth/menu*',
                    'parent_id'   => 1,
                    'order'       => 5,
                    'created_at'  => $createdAt,
                ],
                [
                    'id'          => 6,
                    'name'        => 'Extension',
                    'slug'        => 'extension',
                    'http_method' => '',
                    'http_path'   => '/auth/extensions*',
                    'parent_id'   => 1,
                    'order'       => 6,
                    'created_at'  => $createdAt,
                ],
                [
                    "id" => 7,
                    "name" => "Tunnels",
                    "slug" => "tunnels",
                    "http_method" => "",
                    "http_path" => "/tunnels*",
                    "parent_id" => 0,
                    "order" => 7,
                    'created_at'  => $createdAt,
                ]
            ]
        );

        Models\Role::truncate();
        Models\Role::insert(
            [
                [
                    "id" => 1,
                    "name" => "Administrator",
                    "slug" => "administrator",
                    "created_at" => "2020-12-25 21:15:01",
                    "updated_at" => "2020-12-25 21:15:02"
                ]
            ]
        );

        Models\Setting::truncate();
		Models\Setting::insert(
			[

            ]
		);

		Models\Extension::truncate();
		Models\Extension::insert(
			[

            ]
		);

		Models\ExtensionHistory::truncate();
		Models\ExtensionHistory::insert(
			[

            ]
		);

        Administrator::truncate();
        Administrator::create([
            'username'   => 'admin',
            'password'   => bcrypt('admin'),
            'name'       => 'Administrator',
            'created_at' => $createdAt,
        ]);

        // create a role.
        Role::truncate();
        Role::create([
            'name'       => 'Administrator',
            'slug'       => Role::ADMINISTRATOR,
            'created_at' => $createdAt,
        ]);

        // add role to user.
        Administrator::first()->roles()->save(Role::first());

        // pivot tables
        DB::table('admin_permission_menu')->truncate();
		DB::table('admin_permission_menu')->insert(
			[

            ]
		);

        DB::table('admin_role_menu')->truncate();
        DB::table('admin_role_menu')->insert(
            [

            ]
        );

        DB::table('admin_role_permissions')->truncate();
        DB::table('admin_role_permissions')->insert(
            [

            ]
        );

        // finish
        (new Menu())->flushCache();
    }
}
