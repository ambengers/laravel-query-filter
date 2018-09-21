<?php

namespace Ambengers\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Ambengers\QueryFilter\Console\QueryFilterMakeCommand;

class QueryFilterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/query_filter.php' => config_path('query_filter.php'),
        ], 'query_filter');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/query_filter.php', 'query_filter'
        );

        if ($this->app->runningInConsole()) {
            $this->commands(QueryFilterMakeCommand::class);
        }
    }
}
