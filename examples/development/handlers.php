<?php

use AmraniCh\AjaxDispatcher\Handler\Handler;

return [

    Handler::get('getPosts', 'PostController@getPosts'),

    Handler::many(['GET', 'POST'], 'getPost', 'PostController@getPost'),

    Handler::post('createPost', 'PostController@createPost'),

    Handler::put('updatePost', 'PostController@updatePost'),

    Handler::delete('removePost', 'PostController@removePost'),
];