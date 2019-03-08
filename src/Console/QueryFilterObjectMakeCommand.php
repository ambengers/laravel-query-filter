<?php

namespace Ambengers\QueryFilter\Console;

use Illuminate\Console\GeneratorCommand;

class QueryFilterObjectMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:query-filter-object {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new query filter object class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'QueryFilterObject';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../Stubs/QueryFilterObject.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('query_filter.filter_namespace');
    }
}
