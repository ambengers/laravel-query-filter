<?php

namespace Ambengers\QueryFilter\Tests;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Route::namespace('Ambengers\QueryFilter\Tests\Controllers')
            ->group(function () {
                Route::get('/posts', 'PostController@index')->name('posts.index');
            });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
