<?php
namespace Deployer;

require 'vendor/autoload.php';
require 'recipe/common.php'; // Add the common recipe

// Configure the application name and repository
set('application', 'ehr');
set('repository', 'https://github.com/janrussel23131/EHR-sksu');

// Server requirements
set('bin/php', function () {
    return which('php');
});

set('bin/composer', function () {
    if (commandExist('composer')) {
        return 'composer';
    }
    if (commandExist('composer.phar')) {
        return 'composer.phar';
    }
    if (test('[ -f {{release_path}}/composer.phar ]')) {
        return '{{release_path}}/composer.phar';
    }
    return 'composer';
});

// Hosts configuration
host('production')
    ->setHostname('192.168.0.101')
    ->set('deploy_path', '/var/www/ehr')
    ->set('branch', 'main')
    ->set('user', 'deployer')
    ->set('port', 2222)   // Try a different SSH port since 22 is refused
    ;

host('staging')
    ->setHostname('your-staging-server.com') // Replace with your server details
    ->set('deploy_path', '/var/www/ehr-staging')
    ->set('branch', 'develop')
    ->set('user', 'deployer');  // SSH user

// Deploy path configuration
set('keep_releases', 3);

// Shared files/dirs between deploys
set('shared_files', [
    '.env'
]);
set('shared_dirs', [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// Writable dirs by web server
set('writable_dirs', [
    'storage',
    'vendor',
]);

// Set writable mode
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0755');
set('writable_chmod_recursive', true);

// Laravel specific tasks
task('artisan:storage:link', function () {
    run('{{bin/php}} {{release_path}}/artisan storage:link');
})->desc('Create the symbolic links for storage');

task('artisan:config:cache', function () {
    run('{{bin/php}} {{release_path}}/artisan config:cache');
})->desc('Create configuration cache');

task('artisan:route:cache', function () {
    run('{{bin/php}} {{release_path}}/artisan route:cache');
})->desc('Create route cache');

task('artisan:view:cache', function () {
    run('{{bin/php}} {{release_path}}/artisan view:cache');
})->desc('Create view cache');

task('artisan:optimize', function () {
    run('{{bin/php}} {{release_path}}/artisan optimize');
})->desc('Optimize Laravel');

task('laravel:permissions', function () {
    run('chmod -R 775 {{release_path}}/storage');
    run('chmod -R 775 {{release_path}}/bootstrap/cache');
})->desc('Set Laravel directory permissions');

// Custom tasks
task('deploy:check_tools', function () {
    $tools = ['git', 'unzip'];
    foreach ($tools as $tool) {
        $result = run("which $tool || echo 'not installed'");
        if ($result === 'not installed') {
            warning("$tool is not installed on the server. Some tasks might fail.");
        }
    }
})->desc('Check if required tools are installed');

task('deploy:create_dirs', function () {
    run('mkdir -p {{deploy_path}}');
    run('mkdir -p {{deploy_path}}/shared');
})->desc('Create necessary directories');

task('deploy:env', function () {
    if (!test('[ -f {{deploy_path}}/shared/.env ]')) {
        upload('.env.example', '{{deploy_path}}/shared/.env');
        writeln('Uploaded .env.example to shared/.env');
        writeln('Remember to update the .env file with proper production values!');
    }
})->desc('Upload .env file if not exists');

// Use the default flow with additional tasks
desc('Deploy your project');
task('deploy', [
    'deploy:check_tools',
    'deploy:create_dirs',
    'deploy:prepare',
    'deploy:env',
    'deploy:vendors',
    'laravel:permissions',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:optimize',
    'deploy:publish',
]);

// If the deployment fails, the task will be unlocked
after('deploy:failed', 'deploy:unlock'); 