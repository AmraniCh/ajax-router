<?php

return [
    'posts' => 'PostsController@getPosts',
    'comments' => ['CommentsController@getCommentByID', 'id'],
];
