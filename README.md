# Laravel Query Filter

This packages provides an elegant way to filter your eloquent models via the request query string.

Inspired by [Laracasts](https://laracasts.com/series/eloquent-techniques/episodes/4)

[![Build Status](https://travis-ci.org/ambengers/laravel-query-filter.svg?branch=master)](https://travis-ci.org/ambengers/laravel-query-filter)
[![StyleCI](https://github.styleci.io/repos/149767189/shield?branch=master)](https://github.styleci.io/repos/149767189)

# Features

This packages allows you to create filters via the request query string. Out of the box, this package also features sorting, pagination and search for your eloquent models.

# Installation

Run the following command in the terminal.

```bash
composer require ambengers/query-filter
```

Then publish the config file by running the following command.

```bash
php artisan vendor:publish --tag=query-filter-config
```

The config file contains the configuration for the namespace and path of the filter classes. The default namespace is `App\Filters` and default path is `app/Filters`.

# Usage

## Method-based Filters

You can generate a filter class using the `make:query-filter` command.

```php
php artisan make:query-filter PostFilter
```

In the filter class, you can also define your own custom filters. For example, lets add a filter for `/posts?published` to get only the published posts:

```php
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

```php
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
Include the filter logic in the invoke method, like so.

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

## Sorting

This package also allows you to sort your models by following `field|direction` syntax, like so.

```php
/** Sorting */
/posts?sort=created_at|desc
```

## Pagination

This package also allows you to paginate your models like so.

```php
/** Pagination */
/posts?page=2
```

Behind the scenes, it uses Laravel's own pagination, which the default `per_page` size is 15. Of course, you can override this behaviour like so.

```php
/** Pagination */
/posts?page=2&per_page=10
```

Note: If pagination keys are not present on the request query string, it will return a collection result.

## Search

This package also allows you to define the columns that are searchable. By default, when you generate a filter class with `make:query-filter` command,
the class will contain a `$searchableColumns` array. Then, list the searchable columns of your model in this array.

```php

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

```php
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

This packages allows you to load relationships of models using the query string.
First, use the `make:query-loader` command to create your loader class.

```php
php artisan make:query-loader PostLoader
```

Then, declare the loader class within your filter class.

```php
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

Then on the loader class, declare the relationships that can be eager/lazy-loaded within `$loadables` array.

```php
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

And that's it! Now use the `load` param on your query string to load relationships.

```php
/posts?load=comments,author
```

Note: Relationships with multiple words can be declared using either camel or snake case within the `$loadables` array.
The package will automatically convert the relationships into snake case which is typically how you will write your relationship methods.
Also, relationships that are not declared in `$loadables` array will not be eager-loaded even if used in query string.

## Using Loader On The Controller@show Action

`Controller@show` action will typically return a single model instance instead of a collection.
However, there are cases that you will need an ability to optionally load relationships via query string as well.

Inject your loader class as an argument to your `show` method, then call the `filter` method on your and pass the loader instance.

```php
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

```php
/posts/1?load=comments,author
```

## Including Soft Delete Constraints

Include soft deleted constraits when requesting for eager loaded models using the pipe symbol.

```php
/posts/1?load=comments|withTrashed // comments will include soft deleted models
/posts/1?load=comments|onlyTrashed // comments will include only soft deleted models
```

## Preventing Method Name Clash

To customize the method name you call on your model to use the query filter, just update the value of the `method` key in the query_filter config file.

```php
return [
    // The method to call to use the query filter
    'method' => 'fooBar', // Now call $post->fooBar($loaders)
...
]
```

## With [Laravel Livewire](https://github.com/livewire/livewire)

Livewire follows its own structure when sending requests to the backend. This makes it impossible for query-filter package to automatically read parameters from the request query string. <br><br>
However, you can still manually assign parameters during runtime by resolving your query filter class from the container and set the `parameters` like so...

```php

public function render () {
    $filters = app(PostFilter::class)->parameters(['search' => 'foo']);

    $posts = Post::filter($filters);

    return view('livewire.posts.index', ['posts' => $posts]);
}
```

## Similar Packages

[cerbero90/query-filters](https://github.com/cerbero90/query-filters)
