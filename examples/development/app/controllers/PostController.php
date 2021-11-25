<?php

declare (strict_types = 1);

use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Http\Response;

class PostController
{
    
    public function getPosts(): Response
    {
        return Response::json([
            [
                'id'      => 1,
                'title'   => 'Post 1 title',
                'content' => 'Post 1 content',
            ],
        ]);
    }

    public function getPost(Request $request): Response
    {
        return Response::json([
            'id'      => $request->id,
            'title'   => "Post $request->id title",
            'content' => "Post $request->id content"
        ]);
    }

    public function createPost(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'messsage' => "The post with title \"$request->title\" created successfully!"
        ]);
    }

    public function updatePost(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'messsage' => "The post with title \"$request->title\" updated successfully!"
        ]);
    }

    public function removePost(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'messsage' => "The post with id = $request->id deleted successfully!"
        ]);
    }
}
