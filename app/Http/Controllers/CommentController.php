<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\CommentRequest;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'data' => Comment::all()->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CommentRequest $request)
    {
        $validatedData = $request->validated();
        $parent = $validatedData['parent_id'] !== null
            ? Comment::find($validatedData['parent_id'])
            : null;

        $comment = Comment::store([
            'name' => $request->get('name'),
            'level' => $parent ? $parent->level + 1 : Comment::ROOT,
            'message' => $request->get('message'),
            'parent_id' => $request->get('parent_id')
        ]);

        return response()->json([
            'data' => $comment->toArray() ?? []
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CommentRequest $request, int $commentId)
    {
        $comment = Comment::find($commentId);
        $httpCode = 404;

        if ($comment) {
            $data = $comment->updateWith($request->validated() + ['updated_at' => now()]);

            $data = [
                'name' => $data->name,
                'message' => $data->message
            ];
            $httpCode = 204;
        }

        return response()->json([
            'data' => $data ?? [],
        ], $httpCode);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $commentId)
    {
        (Comment::find($commentId))->selfDestroy();
        return response([], 204);
    }
}
