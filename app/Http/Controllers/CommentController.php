<?php

namespace App\Http\Controllers;

use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'data' => DB::table('comments')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $parentId = $request->get('parent_id');
        $parent = $parentId !== null
            ? Comment::getById($parentId)
            : null;

        throw_if(
            $parent && $parent->isLeaf(),
            ValidationException::withMessages(['parent' => 'This comment does not accept comments'])
        );

        try {
            $comment = (new \App\Comment)->store([
                'name' => $request->get('name'),
                'level' => $parent ? $parent->level + 1 : Comment::ROOT,
                'message' => $request->get('message'),
                'parent_id' => $request->get('parent_id')
            ]);

            $httpCode = 201;
        } catch (\Error $e) {
          dd($e);
        } catch (\Exception $e) {
            $httpCode = 400;
            dd($e);
        } finally {
            return response()->json([
                'data' => $comment->toArray() ?? []
            ], $httpCode);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Comment $comment)
    {
        return response()->json([
            'data' => $comment->updateWith($request->all())->toArray(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->selfDestroy();

        return response([], 204);
    }
}
