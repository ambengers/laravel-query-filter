<?php

namespace Ambengers\QueryFilter\Tests\Feature;

use Ambengers\QueryFilter\Tests\FeatureTest;
use Ambengers\QueryFilter\Tests\Filters\PostFilterInterface;
use Ambengers\QueryFilter\Tests\Filters\PostMethodBasedFilters;
use Ambengers\QueryFilter\Tests\Models\Comment;
use Ambengers\QueryFilter\Tests\Models\Post;

class MethodBasedFilterTest extends FeatureTest
{
    protected function setUp() : void
    {
        parent::setUp();

        app()->bind(PostFilterInterface::class, function () {
            return new PostMethodBasedFilters(request());
        });
    }

    /** @test */
    public function it_can_sort()
    {
        $this->withoutExceptionHandling();

        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $post2 = factory(Post::class)->create(['subject' => 'bang bang']);

        $response = $this->getJson(route('posts.index', ['sort' => 'subject|asc']))
            ->assertSuccessful();

        $results = collect(json_decode($response->content()));

        $this->assertTrue($results->first()->id === $post2->id);
        $this->assertTrue($results->last()->id === $post1->id);
    }

    /** @test */
    public function it_can_paginate()
    {
        $this->withoutExceptionHandling();

        factory(Post::class, 15)->create();

        $response = $this->getJson(route('posts.index', ['page' => 1]))
            ->assertSuccessful();

        $results = collect(json_decode($response->content()));

        $this->assertTrue($results->has('current_page'));
        $this->assertTrue($results->has('first_page_url'));
        $this->assertTrue($results->has('last_page'));
        $this->assertTrue($results->has('last_page_url'));
    }

    /** @test */
    public function it_sorts_the_entire_collection_when_there_is_pagination()
    {
        $posts = factory(Post::class, 30)->create();

        $response = $this->getJson(route('posts.index', ['page' => 1, 'per_page' => 10, 'sort=id|desc']))
            ->assertSuccessful();

        $this->assertTrue($response->data()->first()->is($posts->last()));

        $response = $this->getJson(route('posts.index', ['page' => 3, 'per_page' => 10, 'sort=id|desc']))
            ->assertSuccessful();

        $this->assertTrue($response->data()->last()->is($posts->first()));
    }

    /** @test */
    public function it_can_detect_per_page_pagination()
    {
        factory(Post::class, 100)->create();

        $response = $this->getJson(route('posts.index', ['page' => 2, 'per_page' => 10]))
            ->assertSuccessful();

        $results = collect(json_decode($response->content()));

        $this->assertTrue($results['current_page'] == 2);
        $this->assertTrue($results['per_page'] == 10);

        $response = $this->getJson(route('posts.index', ['page' => 3, 'per_page' => 5]))
            ->assertSuccessful();

        $results = collect(json_decode($response->content()));

        $this->assertTrue($results['current_page'] == 3);
        $this->assertTrue($results['per_page'] == 5);
    }

    /** @test */
    public function it_can_filter_by_comment_id()
    {
        $this->withoutExceptionHandling();

        $post1 = factory(Post::class)->create();
        $comment1 = factory(Comment::class)->create([
            'post_id' => $post1->id,
            'body'    => 'Commenting out loud',
        ]);

        $post2 = factory(Post::class)->create();
        $comment2 = factory(Comment::class)->create([
            'post_id' => $post2->id,
            'body'    => 'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['comments' => $comment1->id]))
            ->assertSuccessful();

        $response->assertJsonFragment([
            'subject' => $post1->subject,
            'body'    => $post1->body,
        ]);

        $response->assertJsonMissing([
            'subject' => $post2->subject,
            'body'    => $post2->body,
        ]);
    }
}
