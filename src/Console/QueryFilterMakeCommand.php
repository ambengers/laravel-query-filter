<?php

namespace Ambengers\QueryFilter\Console;

use Illuminate\Console\GeneratorCommand;

class QueryFilterMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:query-filter {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new query filter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'QueryFilter';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../QueryFilter.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('query_filter.namespace');
    }

}
