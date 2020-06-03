<?php

/*
 * This file is part of the ziorye/ddeployer.
 *
 * (c) ziorye <ziorye@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Ziorye\DDeployer\Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Ziorye\DDeployer\Tests\TestCase;

class DeployControllerTest extends TestCase
{
    public function testForbiddenInLocalEnvironment()
    {
        $this->app['env'] = 'local';

        $response = $this->post(route('ddeployer.deploy'), $this->preparedData(), [
            'X-Gitlab-Token' => 'random_token',
        ]);

        $response->assertForbidden()
            ->assertSeeText('application is in local environment. Ignoring.');
    }

    public function testForbiddenOnNoSecretTokenFound()
    {
        $this->app['config']->set('ddeployer.secret_token', '');

        $response = $this->post(route('ddeployer.deploy'), $this->preparedData(), [
            'X-Gitlab-Token' => 'random_token',
        ]);

        $response->assertForbidden()
            ->assertSeeText('No secret_token found. Ignoring.');
    }

    public function testForbiddenOnMissingXHeader()
    {
        $response = $this->post(route('ddeployer.deploy'), $this->preparedData());

        $response->assertForbidden()
            ->assertSeeText('Missing X header.');
    }

    public function testCheckBranch()
    {
        $response = $this->post(route('ddeployer.deploy') . '?branch=no-master', $this->preparedData(), [
            'X-Gitlab-Token' => 'random_token',
        ]);

        $response->assertForbidden()
            ->assertSeeText('the ref in payload [refs\/heads\/master] does not match [refs\/heads\/no-master], No need to do any thing');
    }

    public function testEverythingIsOk()
    {
        Storage::fake('local');

        $response = $this->postJson(route('ddeployer.deploy'), $this->preparedData(), [
            'X-Gitlab-Token' => 'random_token',
        ]);

        $response->assertOk();
        $response->assertJsonCount(6);
        Storage::disk('local')->assertExists('deploy.sh');
    }

    private function preparedData($branch = 'master')
    {
        return [
            "ref" => "refs/heads/$branch",
            "commits" => [
                [
                    "added" => [
                        "composer.json",
                    ],
                    "removed" => [
                        "database/migrations/create_test_table.php",
                    ],
                    "modified" => [
                        "composer.lock",
                    ],
                ],
            ],
        ];
    }
}
