<?php
namespace Ambengers\QueryFilter\Tests\Feature;

use Ambengers\QueryFilter\Tests\FeatureTest;
use Ambengers\QueryFilter\Tests\Models\Post;
use Ambengers\QueryFilter\Tests\Models\User;
use Ambengers\QueryFilter\Tests\Models\Comment;
use Ambengers\QueryFilter\Tests\Filters\PostFilterInterface;
use Ambengers\QueryFilter\Tests\Filters\PostMethodBasedFilters;

class SearchFilterTest extends FeatureTest
{
    protected function setUp() : void
    {
        parent::setUp();

        app()->bind(PostFilterInterface::class, function ($app) {
            return new PostMethodBasedFilters(request());
        });
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
            'subject'   =>  $post1->subject,
            'body'      =>  $post1->body,
        ]);

        $response->assertJsonMissing([
            'subject'   =>  $post2->subject,
            'body'      =>  $post2->body,
        ]);
    }

    /** @test */
    public function it_can_search_through_relationships()
    {
        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $comment1 = factory(Comment::class)->create([
            'post_id'   =>  $post1->id,
            'body'      =>  'Commenting out loud',
        ]);

        $post2 = factory(Post::class)->create();
        $comment2 = factory(Comment::class)->create([
            'post_id'   =>  $post2->id,
            'body'      =>  'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['search' => 'Commenting']))
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

    /** @test */
    public function it_can_search_and_load_relationships_at_the_same_time()
    {
        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $comment1 = factory(Comment::class)->create([
            'post_id'   =>  $post1->id,
            'body'      =>  'Commenting out loud',
        ]);

        $post2 = factory(Post::class)->create(['subject' => 'flamingo rock']);
        $comment2 = factory(Comment::class)->create([
            'post_id'   =>  $post2->id,
            'body'      =>  'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['search' => 'flamingo', 'load' => 'comments']))
            ->assertSuccessful()
            ->assertJsonFragment(['body' => $comment2->body])
            ->assertJsonFragment(['subject' => 'flamingo rock']);
    }

    /** @test */
    public function it_can_search_through_multiple_levels_of_relationship()
    {
        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $user1 = factory(User::class)->create(['name' => 'Johnny Bravo']);
        $comment1 = factory(Comment::class)->create([
            'post_id'   =>  $post1->id,
            'user_id'   =>  $user1->id,
            'body'      =>  'Commenting out loud',

        ]);

        $post2 = factory(Post::class)->create(['subject' => 'flamingo rock']);
        $user2 = factory(User::class)->create(['name' => 'Lucille Tan']);
        $comment2 = factory(Comment::class)->create([
            'post_id'   =>  $post2->id,
            'user_id'   =>  $user2->id,
            'body'      =>  'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['search' => 'brav']))
            ->assertSuccessful()
            ->assertJsonFragment(['subject' => $post1->subject])
            ->assertJsonMissing(['subject' => $post2->subject]);
    }

    /** @test */
    public function it_can_search_with_multiple_words()
    {
        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $post2 = factory(Post::class)->create(['subject' => 'foobar']);
        $post3 = factory(Post::class)->create(['subject' => 'bang bang']);

        $response = $this->getJson(route('posts.index', ['search' => 'foobar barbazz']))
            ->assertSuccessful();

        $response->assertJsonFragment([
            'subject'   =>  $post1->subject,
            'body'      =>  $post1->body,
        ]);

        $response->assertJsonFragment([
            'subject'   =>  $post2->subject,
            'body'      =>  $post2->body,
        ]);

        $response->assertJsonMissing([
            'subject'   =>  $post3->subject,
            'body'      =>  $post3->body,
        ]);
    }

    /** @test */
    public function it_can_search_with_multiple_words_through_relationship()
    {
        $post1 = factory(Post::class)->create(['subject' => 'foobar barbazz']);
        $comment1 = factory(Comment::class)->create([
            'post_id'   =>  $post1->id,
            'body'      =>  'Commenting out loud',
        ]);

        $post2 = factory(Post::class)->create();
        $comment2 = factory(Comment::class)->create([
            'post_id'   =>  $post2->id,
            'body'      =>  'Dont search',
        ]);

        $response = $this->getJson(route('posts.index', ['search' => 'loud out commenting']))
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
