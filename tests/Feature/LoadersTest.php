<?php

namespace Ambengers\QueryFilter\Tests\Feature;

use Ambengers\QueryFilter\Tests\FeatureTest;
use Ambengers\QueryFilter\Tests\Filters\PostFilterInterface;
use Ambengers\QueryFilter\Tests\Filters\PostMethodBasedFilters;
use Ambengers\QueryFilter\Tests\Filters\ProjectFilters;
use Ambengers\QueryFilter\Tests\Models\Comment;
use Ambengers\QueryFilter\Tests\Models\Deployment;
use Ambengers\QueryFilter\Tests\Models\Environment;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Models\Project;

class LoadersTest extends FeatureTest
{
    protected function setUp() : void
    {
        parent::setUp();

        app()->bind(PostFilterInterface::class, function ($app) {
            return new PostMethodBasedFilters(request());
        });
    }

    /** @test */
    public function it_can_load_relationships()
    {
        $this->withoutExceptionHandling();

        $post = factory(Post::class)->create(['subject' => 'foobar barbazz']);

        $comment1 = factory(Comment::class)->create(['post_id' => $post->id]);
        $comment2 = factory(Comment::class)->create(['post_id' => $post->id]);
        $comment3 = factory(Comment::class)->create(['post_id' => $post->id]);

        $response = $this->getJson(route('posts.index', ['load' => 'comments']))
            ->assertSuccessful()
            ->assertJsonFragment(['body' => $comment1->body])
            ->assertJsonFragment(['body' => $comment2->body])
            ->assertJsonFragment(['body' => $comment3->body]);
    }

    /** @test */
    public function it_can_load_relationships_from_show()
    {
        $this->withoutExceptionHandling();

        $post1 = factory(Post::class)->create(['subject' => 'Post One']);
        $post2 = factory(Post::class)->create(['subject' => 'Second Sample']);
        $post3 = factory(Post::class)->create(['subject' => 'foobar barbazz']);

        $comment1 = factory(Comment::class)->create(['post_id' => $post3->id]);
        $comment2 = factory(Comment::class)->create(['post_id' => $post3->id]);
        $comment3 = factory(Comment::class)->create(['post_id' => $post3->id]);

        $response = $this->getJson(route('posts.show', ['post' => $post3, 'load' => 'comments']))
            ->assertSuccessful()
            ->assertJsonFragment(['subject' => $post3->subject])
            ->assertJsonFragment(['body' => $comment1->body])
            ->assertJsonFragment(['body' => $comment2->body])
            ->assertJsonFragment(['body' => $comment3->body]);

        $this->assertTrue($response->data() instanceof Post);
    }

    /** @test */
    public function it_can_paginate_and_load_relationships_at_the_same_time()
    {
        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $comment1 = factory(Comment::class)->create([
            'post_id' => $post1->id,
            'body' => 'Commenting out loud',
        ]);

        $post2 = factory(Post::class)->create(['subject' => 'flamingo rock']);
        $comment2 = factory(Comment::class)->create([
            'post_id' => $post2->id,
            'body' => 'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['page' => 1, 'per_page' => 1, 'load' => 'comments']))
            ->assertSuccessful()
            ->assertJsonFragment(['body' => $comment1->body])
            ->assertJsonFragment(['subject' => 'foobar barbazz'])
            ->assertJsonMissing(['body' => $comment2->body])
            ->assertJsonMissing(['subject' => 'flamingo rock']);

        $results = collect(json_decode($response->content()));

        $this->assertTrue($results->has('current_page'));
        $this->assertTrue($results->has('first_page_url'));
        $this->assertTrue($results->has('last_page'));
        $this->assertTrue($results->has('last_page_url'));
    }

    /** @test */
    public function can_load_soft_deleted_relationships()
    {
        $this->withoutExceptionHandling();

        $post = factory(Post::class)->create(['subject' => 'foobar barbazz']);

        $comment1 = factory(Comment::class)->create([
            'post_id' => $post->id,
            'body' => 'Commenting out loud',
        ]);

        $comment2 = factory(Comment::class)->create([
            'post_id' => $post->id,
            'body' => 'I have been deleted!',
            'deleted_at' => now(),
        ]);

        $response = $this->getJson(
            route('posts.show', [
                'post' => $post->id,
                'load' => 'comments|withTrashed'
            ])
        )->assertSuccessful()
        ->assertJsonFragment(['body' => $comment1->body])
        ->assertJsonFragment(['body' => $comment2->body]);
    }

    /** @test */
    public function can_load_comments_with_multiple_constraints()
    {
        $post = factory(Post::class)->create(['subject' => 'foobar barbazz']);

        $comment1 = factory(Comment::class)->create([
            'post_id' => $post->id,
            'body' => 'Commenting out loud',
        ]);

        $comment2 = factory(Comment::class)->create([
            'post_id' => $post->id,
            'body' => 'I have been deleted!',
            'deleted_at' => now(),
        ]);

        $comment3 = factory(Comment::class)->create([
            'post_id' => $post->id,
            'body' => 'I am not approved!',
            'approved_at' => null,
        ]);

        $response = $this->getJson(
            route('posts.show', [
                'post' => $post->id,
                'load' => 'comments|withTrashed,approved'
            ])
        )->assertSuccessful()
        ->assertJsonFragment(['body' => $comment1->body])
        ->assertJsonFragment(['body' => $comment2->body])
        ->assertJsonFragment(['body' => $comment3->body]);

        $response = $this->getJson(
            route('posts.show', [
                'post' => $post->id,
                'load' => 'comments|onlyTrashed'
            ])
        )->assertSuccessful()
        ->assertJsonMissing(['body' => $comment1->body])
        ->assertJsonFragment(['body' => $comment2->body])
        ->assertJsonMissing(['body' => $comment3->body]);
    }

    /** @test */
    public function can_load_has_many_through()
    {
        $proj1 = factory(Project::class)->create();
        $proj2 = factory(Project::class)->create();

        $env1 = factory(Environment::class)->create(['project_id' => $proj1->id]);
        $env2 = factory(Environment::class)->create(['project_id' => $proj1->id]);
        $env3 = factory(Environment::class)->create(['project_id' => $proj2->id]);
        $env4 = factory(Environment::class)->create(['project_id' => $proj2->id]);

        $d1 = factory(Deployment::class)->create(['environment_id' => $env1->id]);
        $d2 = factory(Deployment::class)->create(['environment_id' => $env1->id]);
        $d3 = factory(Deployment::class)->create(['environment_id' => $env4->id]);
        $d4 = factory(Deployment::class)->create(['environment_id' => $env4->id]);

        $filters = app(ProjectFilters::class)->parameters(['load' => 'deployments']);

        $projects = Project::filter($filters);

        $this->setResponseContent($projects)
            ->assertJsonFragment(['id' => $d1->id, 'commit_hash' => $d1->commit_hash])
            ->assertJsonFragment(['id' => $d2->id, 'commit_hash' => $d2->commit_hash])
            ->assertJsonFragment(['id' => $d3->id, 'commit_hash' => $d3->commit_hash])
            ->assertJsonFragment(['id' => $d4->id, 'commit_hash' => $d4->commit_hash]);
    }
}
