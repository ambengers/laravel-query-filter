<?php

namespace Ambengers\QueryFilter\Tests\Feature;

use Ambengers\QueryFilter\Tests\FeatureTest;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Models\Comment;
use Ambengers\QueryFilter\Tests\Filters\PostFilterInterface;
use Ambengers\QueryFilter\Tests\Filters\PostObjectBasedFilters;

class ObjectBasedFilterTest extends FeatureTest
{
    protected function setUp() : void
    {
        parent::setUp();

        app()->bind(PostFilterInterface::class, function ($app) {
            return new PostObjectBasedFilters(request());
        });
    }

    /** @test */
    public function can_filter_by_comment_id_through_object_based_filter()
    {
        $this->withoutExceptionHandling();

        $post1 = factory(Post::class)->create();
        $comment1 = factory(Comment::class)->create([
            'post_id'   =>  $post1->id,
            'body'      =>  'Commenting out loud',
        ]);

        $post2 = factory(Post::class)->create();
        $comment2 = factory(Comment::class)->create([
            'post_id'   =>  $post2->id,
            'body'      =>  'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['comments' => $comment1->id]))
            ->assertSuccessful();

        $response->assertJsonFragment([
            'subject'   =>  $post1->subject,
            'body'      =>  $post1->body,
        ]);

        $response->assertJsonMissing([
            'subject'   =>  $post2->subject,
            'body'      =>  $post2->body,
        ]);
    }
}
