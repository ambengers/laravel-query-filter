<?php

namespace Ambengers\QueryFilter\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Filters\PostFilters;

class PostController extends Controller
{
    public function index(PostFilters $filters)
    {
    	$posts = Post::filter($filters);

    	return response()->json($posts);
    }

}
