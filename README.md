## Laravel Query Filter
This packages provides an elegant way to filter your eloquent models via the request query string.

Inspired by [Laracasts](https://laracasts.com/series/eloquent-techniques/episodes/4)

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
composer require "ambengers/query-filter":"dev-master"
```

Optionally, you can publish the config file by running the following command.
``` bash
php artisan vendor:publish --tag=query_filter
```
The config file contains the configuration for the namespace and path of the filter classes.

## Usage
Make your model use the `QueryFilterable` trait
``` php
use Ambengers\QueryFilter\QueryFilterable;

class Post extends Model
{
  use QueryFilterable;
}
```

Then you can make a filter class using the `make:query-filter` command.
``` php
php artisan make:query-filter PostFilter
```

In this filter class, you can also define your own custom filters. For example, lets add a filter for `/posts?published` to get only the published posts:
``` php
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

## Caveats
1. This package automatically detects pagination when request query string has `page` and/or `per_page` keys.
2. If pagination keys are not present on the request query string, it will return a collection result.
3. Pagination when combined with Sorting only paginates the current page that is being requested.

## Similar Packages
[cerbero90/query-filters](https://github.com/cerbero90/query-filters)