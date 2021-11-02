<?php

declare(strict_types=1);

/**
 * A fake controller for testing.
 */
class CommentsController
{

    public function getCommentByID($ID): string
    {
        return json_encode([[
            'id' => 1,
            'content' => "Comment $ID content",
        ]]);
    }
}
