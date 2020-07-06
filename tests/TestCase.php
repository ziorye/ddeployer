<?php

/*
 * This file is part of the ziorye/ddeployer.
 *
 * (c) ziorye <ziorye@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Ziorye\DDeployer\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ziorye\DDeployer\DDeployerServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            DDeployerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('ddeployer.secret_token', 'random_token');
        $app['config']->set('ddeployer.commands', [
            'before' => [
                'php artisan down --message="Auto deployment in progress..."',
                'git fetch origin {$branch}',
                'git reset --hard origin/{$branch}',
            ],
            'custom' => [
                'composer.json,composer.lock' => ['php composer install --no-ansi --no-interaction --no-dev --no-suggest --no-progress --prefer-dist'],
                'database/migrations' => ['php artisan migrate --force'],
            ],
            'after' => [
                'php artisan up',
            ],
        ]);
        $app['config']->set('ddeployer.default_branch_to_be_pull', 'master');
        $app['config']->set('ddeployer.extra_check', false);
        $app['config']->set('ddeployer.php_bin_path', '/usr/bin/php');
        $app['config']->set('ddeployer.git_bin_path', '/usr/bin/git');
        $app['config']->set('ddeployer.composer_bin_path', '/usr/bin/composer');
    }
}
