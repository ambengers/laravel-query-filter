<?php

namespace Ambengers\QueryFilter\Tests\Feature;

use Ambengers\QueryFilter\Tests\FeatureTest;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Models\Comment;

class QueryFilterTest extends FeatureTest
{
	protected function setUp()
	{
		parent::setUp();
	}

	/** @test */
	public function it_can_search_for_models()
	{
		$this->withoutExceptionHandling();

		$post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
		$post2 = factory(Post::class)->create(['subject' => 'bang bang']);

		$response = $this->getJson(route('posts.index', ['search' => 'foobar']))
			->assertSuccessful();

		$response->assertJsonFragment([
			'subject'	=>	$post1->subject,
			'body'		=>	$post1->body,
		]);

		$response->assertJsonMissing([
			'subject'	=>	$post2->subject,
			'body'		=>	$post2->body,
		]);
	}

	/** @test */
	public function it_can_search_through_relationships()
	{
		$post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
		$comment1 = factory(Comment::class)->create([
			'post_id'	=>	$post1->id,
			'body' 		=> 	'Commenting out loud',
		]);

		$post2 = factory(Post::class)->create();
		$comment2 = factory(Comment::class)->create([
			'post_id'	=>	$post2->id,
			'body' 		=> 	'Dont search',
		]);

		$response = $this->getJson(route('posts.index', ['search' => 'Commenting out loud']))
			->assertSuccessful();

		$response->assertJsonFragment([
			'subject'	=>	$post1->subject,
			'body'		=>	$post1->body,
		]);

		$response->assertJsonMissing([
			'subject'	=>	$post2->subject,
			'body'		=>	$post2->body,
		]);
	}

	/** @test */
	public function it_can_sort()
	{
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
		$post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
		$comment1 = factory(Comment::class)->create([
			'post_id'	=>	$post1->id,
			'body' 		=> 	'Commenting out loud',
		]);

		$post2 = factory(Post::class)->create();
		$comment2 = factory(Comment::class)->create([
			'post_id'	=>	$post2->id,
			'body' 		=> 	'Dont search',
		]);

		$response = $this->getJson(route('posts.index', ['comments' => $comment1->id]))
			->assertSuccessful();

		$response->assertJsonFragment([
			'subject'	=>	$post1->subject,
			'body'		=>	$post1->body,
		]);

		$response->assertJsonMissing([
			'subject'	=>	$post2->subject,
			'body'		=>	$post2->body,
		]);
	}



}