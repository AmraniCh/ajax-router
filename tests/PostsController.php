<?php

class PostsController {

    public function getPosts()
    {
        return json_encode([[
                'id' => 1,
                'title' => 'Post 1 title',
                'content' => 'Post 1 content',
            ]
        ]);
    }

    public function getPostsByID($id)
    {
        return json_encode([
            'id' => $id,
            'title' => "Post $id title",
            'content' => "Post $id content",
        ]);
    }
}
