<?php

namespace Tests\Feature\Api;

use App\Comment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_comments()
    {
        $comment = factory(Comment::class)->create();

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

    public function test_store_a_comment()
    {
        $comment = factory(Comment::class)->make();

        $response = $this->post(route('comments.store'), $comment->toArray());
        $response->assertJson([
            'data' => ['name' => $comment->name, 'message' => $comment->message ]
        ]);
        $this->assertDatabaseHas('comments', $comment->toArray());

    }

    public function test_delete_a_comment()
    {
        $comment = factory(Comment::class)->create();
        $this->deleteJson(
            route('comments.destroy', $comment->id),
        );

        $this->assertDatabaseMissing('comments', $comment->toArray());
        dd(Comment::baseQuery()->get());
    }

    public function test_update_a_comment()
    {
        $comment = factory(Comment::class)->create();
        $updates = factory(Comment::class)->make();

        $response = $this->putJson(
            route('comments.update',  $comment->id),
            $updates->toArray(),
        );

        $response->assertJson([
            'data' => [
                'name' => $updates->name,
                'message' => $updates->message,
            ],
        ]);

        $this->assertDatabaseHas('comments', $updates->toArray());
    }

    public function test_not_allowed_store_fourth_level_comment()
    {
        $parent = $this->createNestedComments();
        $fourthLvl = factory(Comment::class)->make(['parent_id' => $parent->id]);

        $response = $this->postJson(
            route('comments.store', $fourthLvl->toArray()),
        );
        $response->assertJsonValidationErrors(['parent']);
        $this->assertEquals($response->getStatusCode(), 422);

    }

    public function test_delete_nested_comments()
    {
        $parentComment = $this->createNestedComments();
        $this->deleteJson(route('comments.destroy', $parentComment->id));
        $this->assertEquals(0, Comment::baseQuery()->get()->count());
    }

    private function createNestedComments(int $lvl = 3)
    {
        $currentComment = $rootComment = factory(Comment::class)->create();

        for($index = 1; $index < $lvl; $index++) {
            $currentComment = factory(Comment::class)->create([
                'level' => $currentComment->level + 1,
                'parent_id' => $currentComment->id,
            ]);
        }

        return $rootComment;
    }
}
