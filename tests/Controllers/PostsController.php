<?php

namespace Ambengers\QueryFilter\Tests\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Filters\PostLoader;
use Ambengers\QueryFilter\Tests\Filters\PostFilters;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  PostFilters $filters
     * @return Illuminate\Http\JsonResponse
     */
    public function index(PostFilters $filters)
    {
        $posts = Post::filter($filters);

        return response()->json($posts);
    }

    /**
     * Display the specified resource.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function show(PostLoader $loader, Post $post)
    {
        $post = $post->filter($loader);

        return response()->json($post);
    }
}
