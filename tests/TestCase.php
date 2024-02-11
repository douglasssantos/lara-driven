<?php

namespace Larakeeps\LaraDriven\Tests;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Larakeeps\LaraDriven\Providers\LaraDrivenServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{

    /**
     * add the package provider
     *
     * @param  Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            LaraDrivenServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'LaraDriven' => LaraDrivenService::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('user', ['username' => 'testcase', 'password' => 'testcase']);
    }
}