<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/app/controllers/PostsController.php';
require_once __DIR__ . '/app/controllers/CommentsController.php';

use AmraniCh\AjaxDispatcher\Dispatcher;

function exceptionHandler($ex)
{
    echo(json_encode(["error" => $ex->getMessage()]));
    exit();
}

try {
    $dispatcher = new Dispatcher($_SERVER, 'handler', require __DIR__ . '/ajax/handlers.php');

    $dispatcher->registerControllers([
        PostsController::class,
        CommentsController::class,
    ]);

    $dispatcher->before(function($params) {
        if (!isset($params->handler)) {
            throw new Exception("No handler name was specified with this AJAX request!");
        }
    });

    $dispatcher->onException(function($ex) {
        exceptionHandler($ex);
    });

    $dispatcher->dispatch();
} catch (DispatcherException $ex) {
    exceptionHandler($ex);
}
