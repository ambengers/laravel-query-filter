<?php

namespace Ambengers\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Ambengers\QueryFilter\Console\QueryFilterMakeCommand;
use Ambengers\QueryFilter\Console\QueryLoaderMakeCommand;
use Ambengers\QueryFilter\Console\QueryFilterObjectMakeCommand;

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

        $this->bootEloquentFilterMacro();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/query_filter.php',
            'query_filter'
        );

        if ($this->app->runningInConsole()) {
            $this->commands(QueryFilterMakeCommand::class);
            $this->commands(QueryLoaderMakeCommand::class);
            $this->commands(QueryFilterObjectMakeCommand::class);
        }
    }

    /**
     * Boot the eloquent builder 'filter' macro.
     *
     * @return mixed
     */
    protected function bootEloquentFilterMacro()
    {
        $method = config('query_filter.method', 'filter');

        Builder::macro($method, function (RequestQueryBuilder $filters) {
            if ($filters instanceof AbstractQueryLoader) {
                return $filters->getFilteredModel($this);
            }

            if ($filters->shouldPaginate()) {
                return $filters->paginate($this);
            }

            return $filters->getFilteredModelCollection($this);
        });
    }
}
