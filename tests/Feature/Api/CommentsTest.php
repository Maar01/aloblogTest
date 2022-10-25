<?php

namespace Tests\Feature\Api;

use App\Comment;
use Database\Factories\CommentFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_comments()
    {
        $comment = CommentFactory::create();

        $response = $this->getJson(route('comments.index'));
        $response->assertOk();
        $response->assertJson([
            'data' => [
                [
                    'id' => $comment->id,
                    'name' => $comment->name,
                    'message' => $comment->message,
                ]
            ],
        ]);
    }

    public function test_can_get_all_comments_with_sub_comments()
    {
        $rootComment = $this->createNestedComments();
        $response = $this->getJson(route('comments.index'));
        $response->assertOk();
        $rootComment->getTreeComments();

        $response->assertJson([
            'data' => [
                json_decode($rootComment->toJson(), true)
            ],
        ]);
    }

    public function test_store_a_comment()
    {
        $comment = CommentFactory::create();

        $response = $this->postJson(route('comments.store'), $comment->toArray());
        $response->assertJson([
            'data' => ['name' => $comment->name, 'message' => $comment->message ]
        ]);
        $this->assertDatabaseHas('comments', $comment->toArray());

    }

    public function test_delete_a_comment()
    {
        $comment = CommentFactory::create();
        $this->deleteJson(
            route('comments.destroy', $comment->id),
        );

        $this->assertDatabaseMissing('comments', $comment->toArray());
    }

    public function test_update_an_existing_comment()
    {
        $comment = CommentFactory::create();
        $updates = CommentFactory::make();

        $this->putJson(
            route('comments.update',  $comment->id),
            $updates->toArray(),
        );

        $this->assertDatabaseHas('comments', $updates->toArray());
    }

    public function test_update_a_non_existing_comment()
    {
        $updates = CommentFactory::make();

        $response = $this->putJson(
            route('comments.update', 100),
            $updates->toArray(),
        );

        $response->assertNotFound();
    }

    public function test_can_not_add_comment_to_non_existing_parent()
    {
        $comment = CommentFactory::make();;
        $comment->parent_id = 100;//non-existing id
        $response = $this->json('POST', route('comments.store'), $comment->toArray());
        $response->assertJsonValidationErrors(['parent_id']);
        $this->assertEquals($response->getStatusCode(), 422);
    }

    public function test_not_allowed_store_fourth_level_comment()
    {
        $parent = $this->createNestedComments();
        $fourthLvl = CommentFactory::make(['parent_id' => $parent->id]);

        $response = $this->postJson(
            route('comments.store', $fourthLvl->toArray()),
        );
        $response->assertJsonValidationErrors(['parent_id']);
        $this->assertEquals($response->getStatusCode(), 422);

    }

    public function test_delete_nested_comments()
    {
        $parentComment = $this->createNestedComments();
        $this->deleteJson(route('comments.destroy', $parentComment->id));
        $this->assertEquals(0, Comment::all()->count());
    }

    public function test_show_comment()
    {
        $rootComment = $this->createNestedComments();
        $response = $this->getJson(route('comments.show', $rootComment->id));
        $response->assertOk();
    }

    private function createNestedComments(int $lvl = 3)
    {
        $currentComment = $rootComment = CommentFactory::create();

        for($index = 1; $index < $lvl; $index++) {
            $currentComment = CommentFactory::create([
                'level' => $currentComment->level + 1,
                'parent_id' => $currentComment->id,
            ]);
        }

        return $rootComment;
    }
}
