<?php

namespace Ambengers\QueryFilter\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\TestResponse;

class FeatureTest extends TestCase
{
	/**
	 * Setup the test environment
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->loadMigrations();

		$this->withFactories(__DIR__.'/Factories');

        TestResponse::macro('data', function ($key = null) {
            if (!$key) { return $this->original; }
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
            '--realpath' => realpath(__DIR__.'/Migrations'),
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
        return ['Ambengers\QueryFilter\Tests\TestServiceProvider'];
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
}