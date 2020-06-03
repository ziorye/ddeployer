<?php

/*
 * This file is part of the ziorye/ddeployer.
 *
 * (c) ziorye <ziorye@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Ziorye\DDeployer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ziorye\DDeployer\Http\Controllers\DeployController;

class DDeployerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/deploy.php' => config_path('ddeployer.php'),
        ], 'config');

        Route::post('ddeployer/deploy', [DeployController::class, 'deploy'])->name('ddeployer.deploy');
    }
}
