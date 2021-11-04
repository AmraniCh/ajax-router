# AmraniCh/AjaxDispatcher

[![tests](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml/badge.svg)](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml)

Handle AJAX requests and send them to an appropriate handler.

## Installation

You can install this library using composer, and because there is only one released version which is a pre-release (beta) 
version, you can choose between these two methods to install it.

### Require the exact version :

```bash
composer require amranich/ajax-dispatcher:v1.0.0-beta
```

### Change the minimum stability for composer  : 

Add this option to your composer.json file :

```
"minimum-stability": "beta"
```

and then run composer install :

```bash
composer install
```

Otherwise, you can download the repo and include the classes in the `src` folder to your application.

## Basic usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/controllers/PostsController.php';
require_once __DIR__ . '/app/controllers/CommentsController.php';

use AmraniCh\AjaxDispatcher\Dispatcher;
use AmraniCh\AjaxDispatcher\DispatcherException;

try {
    $dispatcher = new Dispatcher($_SERVER, 'handler', [
        'GET' => [
            'posts' => 'PostsController@getPosts',
            'comments' =>  ['CommentsController@getCommentByID', 'id'],
        ],
        'POST' => [
            'signIn' => function($id, $name) {
                echo("user with id='$id' and name='$name' is sign in successfully!");
            },
        ],
    ]);

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

function exceptionHandler($ex)
{
    echo(json_encode(["error" => $ex->getMessage()]));
    exit();
}

```

## API Methods

### `__construct(array $server, string $handlerName, array $handlers)`

* **$server :** the server variables.
* **$handlerName :** the handler name to be executed.
* **$handlers :** an associative array that register request handlers.

<hr>

### `registerControllers(array $controllers)`

*Register controllers instances and namespaces.*

* **$controllers :** an array of controller instances or namespaces.

<hr>

### `before(callable $callback)`

*Executes some code before dispatching the current request.*

* **$callback :** the callback function to be executed, all the request parameters will be passed to this callback as a
  first argument.

<hr>

### `onException(callable $callback)`

*Allows to use a custom exception handler for exceptions that may be thrown when calling a handler for the current AJAX
request.*

* **$callback :** a callback function that will accept the exception as a first argument.

<hr>

### `dispatch()`

*Start dispatching the current AJAX request to the appropriate handler (controller method or a callback function).*

## Background

This library lets you route your AJAX requests to a controller method or a specific callback, depending on a specific
request parameter variable that contains the desired function/method name to be executed on the server-side, this
library can be useful for some legacy web applications that not use a URL based routing and not requires some additional
server components to be enabled like the Apache rewrite module for example, the library also can improve the way that
your AJAX requests are handled and help you to write clean code to achieve high maintainable code.

The idea of the library came to my mind a long time ago when I was mostly developing web applications using just plain
PHP, these applications were performing a lot of AJAX requests into a single PHP file, that file can have a hundred
lines that handled this requests depending on function name that sent with the request as a parameter, so I've started
to think of ways to clean up a little this file and improve the readability and make the code more maintainable.

## Inspirations

The way that this README is written is inspired by the README of this
library [mirazmac/dotenvwriter](https://github.com/MirazMac/DotEnvWriter/blob/master/README.md).

## Contribution

All types of contribution are welcome, so do not hesitate to send a PR to add a new feature or just fixing a typo.

## Support

A special thanks to [JetBrains](https://www.jetbrains.com) company for their support to my OSS contributions.

<img width="150" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.png?_gl=1*1evhn6q*_ga*MzA3MTk5NzQ3LjE2MzU3OTk3MDA.*_ga_V0XZL7QHEB*MTYzNTg5MzE3NS4yLjEuMTYzNTg5MzkzNC4xNg..&_ga=2.162913596.1450626373.1635893177-307199747.1635799700"/>

## LICENSE

The library is licensed under the open
source [MIT licence](https://github.com/AmraniCh/ajax-dispatcher/blob/master/LICENSE).