<?php

declare(strict_types=1);

ob_start();
session_start();

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/app/controllers/PostController.php';

use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Http\Response;
use AmraniCh\AjaxDispatcher\Handler\HandlerCollection;
use AmraniCh\AjaxDispatcher\Router;
use AmraniCh\AjaxDispatcher\Dispatcher;

$config = require __DIR__ . '/config.php';

try {
    $request = new Request($_SERVER);

    if ($config['env'] === 'PROD') {
        if (!$request->isAjaxRequest()) {
            Response::raw('bad request.', 400)->send();
            exit();
        }

        if (!array_key_exists('HTTP_REFERER', $request->getHeaders())
            || $request->getHeaderValue('HTTP_REFERER') !== $config['domain']) {
            Response::raw('bad request.', 400)->send();
            exit();
        }

        if (!array_key_exists('HTTP_X_CSRF_TOKEN', $request->getHeaders())) {
            Response::raw("CSRF token not sent.", 400)->send();
            exit();
        }

        if ($request->getHeaders()['HTTP_X_CSRF_TOKEN'] !== $_SESSION['CSRF_TOKEN']) {
            Response::raw("Invalid CSRF token.", 400)->send();
            exit();
        }
    }

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
    global $config;
    
    if ($config['env'] === 'DEV') {
        Response::json(['error' => $ex->getMessage()], $ex->getCode())->send();
    } elseif ($config['env'] === 'PROD') {
        Response::raw('Somethong goes wrong!', 500)->send();
    }

    exit();
}
