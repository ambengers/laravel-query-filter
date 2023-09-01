<?php

namespace Ambengers\QueryFilter\Tests;

use Ambengers\QueryFilter\Tests\Controllers\ModelWithoutTimestampsController;
use Ambengers\QueryFilter\Tests\Controllers\PostsController;
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
            ->middleware('web')
            ->group(function () {
                Route::get('/posts', [PostsController::class, 'index'])->name('posts.index');
                Route::get('/posts/{post}', [PostsController::class, 'show'])->name('posts.show');

                Route::get('/model-without-timestamps', [ModelWithoutTimestampsController::class, 'index'])->name('model-without-timestamps.index');
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
