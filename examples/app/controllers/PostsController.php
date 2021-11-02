<?php

declare(strict_types=1);

/**
 * A fake controller for testing.
 */
class PostsController
{
    public function getPosts(): string
    {
        return json_encode([[
            'id' => 1,
            'title' => 'Post 1 title',
            'content' => 'Post 1 content',
        ]]);
    }

    public function getPostsByID(int $id): string
    {
        return json_encode([
            'id' => $id,
            'title' => "Post $id title",
            'content' => "Post $id content",
        ]);
    }
}
