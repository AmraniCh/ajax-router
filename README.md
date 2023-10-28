[![packagist](https://img.shields.io/packagist/v/AmraniCh/ajax-router?include_prereleases)](https://packagist.org/packages/amranich/ajax-router)
[![tests](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml/badge.svg)](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml)
![License](https://img.shields.io/packagist/l/AmraniCh/ajax-router)


## Getting Started

```bash
composer require amranich/ajax-router
```

You can copy/paste this code snippet for a quick start.

We're using [Guzzle PSR-7 interface implementation](https://github.com/guzzle/psr7) here, but you can use any other library you like as long as it implements the same interface.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use AmraniCh\AjaxRouter\Route;
use AmraniCh\AjaxRouter\Router;
use AmraniCh\AjaxRouter\Dispatcher;
use GuzzleHttp\Psr7\ServerRequest;
use Lazzard\Psr7ResponseSender\Sender;

try {
    $request = ServerRequest::fromGlobals();
    
    // the 'X-requested-with' header is commonly used to inform the server that a request 
    // is sent via the XMLHttpRequest object on the client-side, a lot of JavaScript libraries 
    // like jQuery already send this header automatically, this check can add a small security
    // layer to your app because HTTP headers can be spoofed very easily, so don't count on
    // only that check.
    if (!$request->hasHeader('X-requested-with') 
        || strtolower($request->getHeader('X-requested-with')[0]) !== 'XMLHttpRequest') {
        throw new BadRequestException("Accept only AJAX requests.");
    }
    
    // to organize your project, you can put your routes in a separate file like in an array
    // and require it in the second parameter of the router constructor.  
    $router = new Router($request, 'route', [
    
        // ?route=getPost&id=1005
        Route::get('getPost', function ($params) {
        
            // PSR7 responses are a little annoying to work with, you always have extra HTTP layers 
            // in your app that extend the base PSR7 response class, think of a class like JsonResponse, 
            // and in the constructor add the content-type header and pass it to the parent class.
            $response = new Response;

            $response->getBody()->write(json_encode([
                'id' => $params['id'],
                'title' => 'Best Places to Visit in Marrakech',
                'description' => 'Example of post description',
                'created_at' => '2022-02-27 03:00:05'
            ]));

            return $response->withHeader('Content-type', 'application/json');
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

    $sender = new Sender;
    $sender($response);
}
```

## Usage Tips

### Route to controller/class method

If you like to put the business logic in a separate class or in a controller, you can route your requests to them like this :

```php
Route::get('getPost', [PostController::class, 'getPost']);
```

Or :

```php
Route::get('getPost', 'PostController@getPost');

// register the controller class or instance in the router
$router->registerControllers([
    PostController::class,
]);
```

If the controller/class has some dependencies that must be passed within the constructor, you can still instantiate the
controller on yourself :

```php
$router->registerControllers([
    new PostController($dependencyOne, $dependencyTwo)
]);
```

### Catch route actions exceptions

*I want to catch exceptions that only occurs from my routes actions, and not those thrown by the library or somewhere else, how I can
do that ?*

Answer :

```php
$dispatcher->onException(function (\Exception $ex) {
    // $ex exception thrown by a route action
});
```

### Get current route

You can access the current route object using the static method `getCurrentRoute` of the `Route` class.

```php
$route = Router::getCurrentRoute();
$route->getName();
$route->getMethods();
$route->getValue();

```

## Background

The idea of the library came to my mind a long time ago when I was mostly developing web applications using just plain PHP, some of these applications were performing a lot of AJAX requests into a single PHP file, that file can have a hundred lines of code that process these requests depending on a function/method name that sent along with the request, so I started to think of what I can do to improve the way that these requests are handled, and improve the code readability and maintainability.

## Prizes

This package wins the PHP Innovation Award (February 2022) Issued by phpclasses.org 

🏆 Prize :
One elePHPant mascot.
https://www.php.net/elephpant.php

📜 Certificate :
https://amranich.dev/certs/phpclasses-february-2022-innovation-award.pdf

## They support this project

<img width="150px" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_square.png"/>

