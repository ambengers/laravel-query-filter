<?php

namespace Ambengers\QueryFilter\Tests;

use Illuminate\Http\Response;
use Orchestra\Testbench\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Config;

class FeatureTest extends TestCase
{
    /**
     * Setup the test environment
     *
     * @return  void
     */
    protected function setUp() : void
    {
        parent::setUp();

        Config::set('query_filter.method', 'filter');
        Config::set('query_filter.filter_namespace', 'App\Filters');
        Config::set('query_filter.loader_namespace', 'App\Loaders');

        $this->loadMigrations();

        $this->withFactories(__DIR__.'/Factories');

        TestResponse::macro('data', function ($key = null) {
            if (! $key) {
                return $this->original;
            }
            if ($this->original instanceof Collection) {
                return $this->original->{$key};
            }
            return $this->original->getData()['key'];
        });
    }

    /**
     * Load the migrations for the test environment.
     *
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--path'     => realpath(__DIR__.'/Migrations'),
        ]);
    }

    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Ambengers\QueryFilter\Tests\TestServiceProvider',
            'Ambengers\QueryFilter\QueryFilterServiceProvider',
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Set the data to the response object as content
     *
     * @param mixed $data
     */
    public function setResponseContent($data)
    {
        return TestResponse::fromBaseResponse(
            app(Response::class)->setContent($data)
        );
    }
}
