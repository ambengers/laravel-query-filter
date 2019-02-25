<?php

namespace Ambengers\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Ambengers\QueryFilter\Console\QueryFilterMakeCommand;
use Ambengers\QueryFilter\Console\QueryLoaderMakeCommand;

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
        }
    }

    /**
     * Boot the eloquent builder 'filter' macro.
     *
     * @return mixed
     */
    protected function bootEloquentFilterMacro()
    {
        /*
         * Filter a query.
         *
         * @param  Illuminnate\Database\Eloquent\Builder $query
         * @param  Ambengers\QueryFilter\RequestQueryBuilder $filters
         * @return mixed
         */
        Builder::macro('filter', function (RequestQueryBuilder $filters) {
            if ($filters instanceof AbstractQueryLoader) {
                return $filters->getFilteredModel($this);
            }

            if ($filters->shouldPaginate()) {
                return $filters->getPaginated($this);
            }

            return $filters->getFilteredModelCollection($this);
        });
    }
}
