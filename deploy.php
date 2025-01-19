<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/MercyCloudTeam/TunnelBroker.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/TunnelBroker');

// Hooks

after('deploy:failed', 'deploy:unlock');
