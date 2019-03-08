## Laravel Query Filter
This packages provides an elegant way to filter your eloquent models via the request query string.

Inspired by [Laracasts](https://laracasts.com/series/eloquent-techniques/episodes/4)

[![Build Status](https://travis-ci.org/ambengers/laravel-query-filter.svg?branch=master)](https://travis-ci.org/ambengers/laravel-query-filter)
[![StyleCI](https://github.styleci.io/repos/149767189/shield?branch=master)](https://github.styleci.io/repos/149767189)
## Features
This packages allows you to create filters via the request query string. By default, it offers sorting, pagination and search using the following syntax:

``` php
/** Sorting */
/posts?sort=created_at|asc

/** Pagination */
/posts?page=2&per_page=10

/** Search */
/posts?search=foobar
```

## Installation
Run the following command in the terminal.
``` bash
composer require "ambengers/query-filter":"^3.0"
```

Optionally, you can publish the config file by running the following command.
``` bash
php artisan vendor:publish --tag=query_filter
```
The config file contains the configuration for the namespace and path of the filter classes. The default namespace is `App\Filters` and default path is `app/Filters`.

# Usage
## Method-based Filters
Then you can make a filter class using the `make:query-filter` command.
``` php
php artisan make:query-filter PostFilter
```

In the filter class, you can also define your own custom filters. For example, lets add a filter for `/posts?published` to get only the published posts:
``` php
use Ambengers\QueryFilter\AbstractQueryFilter;

class PostFilter extends AbstractQueryFilter
{
  /**
   * Filter the post to get the published ones
   *
   * @return Illuminate\Database\Eloquent\Builder
   */
  public function published()
  {
    return $this->builder->whereNotNull('published_at');
  }
}
```

Now, you can apply the filter on your controller. For example:
``` php
use App\Filters\PostFilter;

class PostController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(PostFilter $filters)
  {
    $posts = Post::filter($filters);

    return PostResource::collection($posts);
  }
}
```

## Object-based Filters
If you like a more object oriented approach for creating your filters, you can create a `$filters` array in your Filter class to declare your filters.
```php
use Ambengers\QueryFilter\AbstractQueryFilter;

class PostFilter extends AbstractQueryFilter
{
  /**
   * List of filters.
   *
   * @var array
   */
  protected $filters = [
    'published' =>  \App\Filters\Published::class,
  ];
}
```
`$filters` array will receive a key-value pair in which the key is the param in your query string and the value is the filter object that will handle the filtering.

Then you can use the `make:query-filter-object` command to generate your filter object.
Note: filter objects will use the same namespace as your filter class.
```php
php artisan make:query-filter-object Published
```

The filter object is a simple invokable class that accepts the `Eloquent\Builder` as first parameter and the query string value as the second parameter.
You can then put the filter logic in the invoke method.
```php
use Illuminate\Database\Eloquent\Builder;

class Published
{
  /**
   * Handle the filtering
   *
   * @param  Illuminate\Database\Eloquent\Builder $builder
   * @param  string|null  $value
   * @return Illuminate\Database\Eloquent\Builder
   */
  public function __invokable(Builder $builder, $value = null)
  {
    $builder->whereNotNull('published_at');
  }
}
```

## Searchable Columns
This package allows you to define the columns that are searchable. By default, when you generate a filter class with `make:query-filter` command,
the class will contain a `$searchableColumns` array. You can then include your searchable columns of your model in this array.
``` php

class PostFilter extends AbstractQueryFilter
{
  /**
   * List of searchable columns
   *
   * @var array
   */
  protected $searchableColumns = ['subject', 'body'];
}
```

## Searchable Relationship Columns
The `$searchableColumns` can also accept a key value pair if you want your model to be searchable using relationship fields:
``` php
class PostFilter extends AbstractQueryFilter
{
  /**
   * List of searchable columns
   *
   * @var array
   */
  protected $searchableColumns = [
	  'subject',
	  'body',
	  'comments' => ['body'],
  ];
}
```

## Loadable Relationships
This feature allows you to load relationships of models from the query string.
First you will need to use the `make:query-loader` command to create your loader class.

```php
php artisan make:query-loader PostLoader
```

Then you will have to declare the loader class within your filter class.
``` php
use App\Loaders\PostLoader;

class PostFilter extends AbstractQueryFilter
{
  /**
   * Loader class
   *
   * @var string
   */
   protected $loader = PostLoader::class;
}
```

Then on the loader class, you will need to declare the relationships that can be loaded within `$loadables` array.
``` php
class PostLoader extends AbstractQueryLoader
{
    /**
     * Relationships that can be lazy/eager loaded
     *
     * @var array
     */
    protected $loadables = [
        'comments', 'author'
    ];
}
```

And that's it! You can now use the `load` param on your query string to load relationships.
``` php
/posts?load=comments,author
```

Note: Relationships with multiple words can be declared using either camel or snake case within the `$loadables` array.
The package will automatically convert the relationships into snake case which is typically how you will write your relationship methods.
Also, relationships that are not declared in `$loadables` array will not be eager-loaded even if used in query string.

## Using Loader On The Controller@show Action
`Controller@show` action will typically return a single model instance instead of a collection.
However, there are cases that you will need an ability to optionally load relationships via query string as well.

You can inject your loader class as an argument to your `show` method, then call the `filter` method on your and pass the loader instance.
``` php
class PostController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param App\Models\Post $post
     * @param App\Loaders\PostLoader $loader
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Post $post, PostLoader $loader)
    {
        $post = $post->filter($loader);

        return response()->json($post);
    }
}
```

Now you should be able to load your relationships from your query string.
``` php
/posts/1?load=comments,author
```

## Including Soft Delete Constraints
You can include soft deleted constraits when requesting for eager loaded models using the pipe symbol.
```php
/posts/1?load=comments|withTrashed // comments will include soft deleted models
/posts/1?load=comments|onlyTrashed // comments will include only soft deleted models
```

## Preventing Method Name Clash
You can customize the method name you call on your model to use the query filter.
Just update the value of the `method` key in the query_filter config file.
```php
return [
    // The method to call to use the query filter
    'method' => 'fooBar', // You can now call $post->fooBar($loaders)
...
]
```

## Caveats
1. This package automatically detects pagination when request query string has `page` and/or `per_page` keys.
2. If pagination keys are not present on the request query string, it will return a collection result.


## Similar Packages
[cerbero90/query-filters](https://github.com/cerbero90/query-filters)
