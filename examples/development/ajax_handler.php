<?php

declare(strict_types=1);

ob_start();

$root = dirname(dirname(__DIR__));

require $root . '/vendor/autoload.php';
require __DIR__ . '/app/controllers/PostController.php';

use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Http\Response;
use AmraniCh\AjaxDispatcher\Handler\HandlerCollection;
use AmraniCh\AjaxDispatcher\Router;
use AmraniCh\AjaxDispatcher\Dispatcher;

try {
    $request    = new Request($_SERVER);
    $handlers   = new HandlerCollection(require __DIR__ . '/handlers.php');
    $router     = new Router($request, 'handler', $handlers);
    $dispatcher = new Dispatcher($router);

    $router->registerControllers([
        PostController::class
    ]);
 
    $dispatcher
        ->cleanBuffer()
        ->dispatch()
        ->stop();

} catch (\Throwable $ex) {
    Response::json(['error' => $ex->getMessage()], $ex->getCode())->send();
    exit();
}
