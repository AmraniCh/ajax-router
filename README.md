[![packagist](https://img.shields.io/packagist/v/AmraniCh/ajax-router?include_prereleases)](https://packagist.org/packages/amranich/ajax-router)
![php version](https://img.shields.io/packagist/php-v/AmraniCh/ajax-router)
[![tests](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml/badge.svg)](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/AmraniCh/ajax-router/branch/master/graph/badge.svg?token=IFIXJ78PIN)](https://codecov.io/gh/AmraniCh/ajax-router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AmraniCh/ajax-router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/AmraniCh/ajax-router/?branch=master)

## Getting Started

```bash
composer require amranich/ajax-router
```

You can copy/paste this code snippet for a quick start.

We're using Guzzle PSR-7 interface implementation here, but you can use any other library you like as long as it implements the same interface.

```php
?php

require __DIR__ . '/vendor/autoload.php';

use AmraniCh\AjaxRouter\Dispatcher;
use AmraniCh\AjaxRouter\Route;
use AmraniCh\AjaxRouter\Psr7\PSR7ResponseSender;
use AmraniCh\AjaxRouter\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

try {
    $request = ServerRequest::fromGlobals();
    $router = new Router($request, 'route', [

        // ?route=hello&name=john
        Route::get('hello', function ($params) {
            $response = new Response;
            $response->getBody()->write("hello master " . $params["name"]);
            return $response;
        }),
    ]);

    $dispatcher = new Dispatcher($router);
    $dispatcher->dispatch();

} catch (Exception $ex) {
    $response = new Response(
        $ex->getCode() ?: 500,
        ['Content-type' => 'application/json'],
        json_encode(['message' => $ex->getMessage()])
    );

    $sender = new PSR7ResponseSender($response);
    $sender->send();
    exit();
}
```

## Usage Tips

### Route to controller/class method

If you like to put the business logic in a separate class or in a controller, you can route your requests to them like this :

```php
Route::get('getProfile', [UserManager::class, 'getProfile']),
```

Or :

```php
Route::get('getProfile', 'UserManager@getProfile')
```

But you have after to register the controller namespace/instance in the router, like this :

```php
$router->registerControllers([
    UserManager::class,
]);
```

If the controller/class has some dependencies that must be passed within the constructor, you can still instantiate the
controller on yourself :

```php
$router->registerControllers([
    new UserManager($dependencyOne, $dependencyTwo)
]);
```

### Catch handlers exceptions

*I want to catch exceptions that only occurs from my routes actions, and not those thrown by the library or somewhere else, how I can
do that ?*

Answer :

```php
$dispatcher->onException(function (\Exception $ex) {
    // $ex exception thrown by a route action
});
```

## Background

The idea of the library came to my mind a long time ago when I was mostly developing web applications using just plain
PHP, some of these applications were performing a lot of AJAX requests into a single PHP file, that file can have a hundred
lines of code that process these requests depending on a function/method name that send along with the request,
so I started to think of ways to clean up this file to improve the readability and maintainability of the code.

## Buy me a coffee

<a href="https://www.buymeacoffee.com/AmraniCh" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## They support me

A special thanks to [JetBrains](https://www.jetbrains.com) company for their support to my OSS contributions.

<img width="150px" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_square.png"/>


## LICENSE

The library is licensed under the open
source [MIT licence](https://github.com/AmraniCh/ajax-dispatcher/blob/master/LICENSE).
