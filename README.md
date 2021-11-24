# AmraniCh/AjaxDispatcher

[![tests](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml/badge.svg)](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml)
[![coverage](https://img.shields.io/codecov/c/github/AmraniCh/ajax-dispatcher-clone?token=1N5E5MNV8M)](https://app.codecov.io/gh/AmraniCh/ajax-dispatcher)

Handle AJAX requests and send them to an appropriate handler.

Also provides helper classes to simulate HTTP requests and responses.

## Requirements

* php >= 5.6

## Getting Started

```bash
composer require amranich/ajax-dispatcher:v1.0.0-beta2
```

You can copy/paste this code snippet for a quick start.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Handler\HandlerCollection;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use AmraniCh\AjaxDispatcher\Http\Response;
use AmraniCh\AjaxDispatcher\Router;
use AmraniCh\AjaxDispatcher\Dispatcher;

try {
    $request = new Request($_SERVER);

    $handlers = new HandlerCollection([
        
        // ?handler=hello&name=john
        Handler::get('hello', function (Request $request) {
            return Response::raw("Hello $request->name!!");
        }),
    ]);

    $router     = new Router($request, 'handler', $handlers);
    $dispatcher = new Dispatcher($router);

    $dispatcher
        ->cleanBuffer()
        ->dispatch()
        ->stop();

} catch (\Exception $ex) {
    exceptionHandler($ex);
}

function exceptionHandler(\Exception $ex)
{
    Response::json(['error' => $ex->getMessage()], $ex->getCode())->send();
    exit();
}
```

## Route to controller/class method

If you like to put the business logic in a separate class or in a controller, you can route your requests to them like this :

```php
Handler::get('getProfile', [UserManager::class, 'getProfile']),
```

Or :

```php
Handler::get('getProfile', 'UserManager@getProfile')
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

## Catch handlers exceptions

*I want to catch exceptions that only occurs from my AJAX handlers, and not those thrown by the library or somewhere else, how I can
do that ?*

Answer :

```php
$dispatcher->onException(function (\Exception $ex) {
    // $ex exception thrown by a handler
});
```

## Clean output buffer (Recommended)

To be sure that the output (response) for the ajax request it comes only from the defined handlers, and prevent
unexpected echos from sending their content to the browser :

```php
$dispatcher
    ->cleanBuffer()
    ->dispatch();
```

## Stop after dispatching the request (Recommended)

Tells the dispatcher to exit the script right after the handler execution.

```php
$dispatcher
    ->dispatch()
    ->stop();
```

## Security tips for production environments

### 1. Check for the request if it is an "AJAX request"

```php
if (!$request->isAjaxRequest()) {
    Response::raw('bad request.', 400)->send();
    exit();
}
```

**Note :** The `isAjaxRequest` method will look for the `X-REQUESTED-WITH` header in the coming request headers, which
obviously can be spoofed. AJAX requests can be emulated very easily and there is a sure way to know that the request is
definitely an "AJAX request", however, it recommended doing this check.

### 2. Check for your own URL

If you want to add an extra layer of security to your application, you can check for the `HTTP_PREFER` header that hold
the address of the page that the request coming from.

```php
if (!array_key_exists('HTTP_REFERER', $request->getHeaders())
    || $request->getHeaderValue('HTTP_REFERER') !== 'https://www.yourdomain.com/app') {
    Response::raw('bad request.', 400)->send();
    exit();
}
```

**Note :** HTTP headers can be spoofed, that means the content of the `HTTP_PREFER` header cannot be trusted.

### 3. Using CRSF tokens (Recommanded for production servers)

Generate the CRSF token :

```php
session_start(); 
if (!isset($_SESSION['CSRF_TOKEN'])) {
    // random_bytes function introduced as of PHP 7
    $_SESSION['CSRF_TOKEN'] = bin2hex(random_bytes(32));
}
```

Inject the token somewhere in your page, for example in a meta tag :

```html
<meta name="csrf-token" content="<?= $_SESSION['CSRF_TOKEN'] ?>">
```

Send the token in every AJAX request that you made :

```javascript
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN':  $('meta[name="csrf-token"]').attr('content') },
});
```

### Complete example for production usage

I already implemented all these security tips in one example, if you want to check it out see the [examples](examples) folder.

## Background

The idea of the library came to my mind a long time ago when I was mostly developing web applications using just plain
PHP, some of these applications were performing a lot of AJAX requests into a single PHP file, that file can have a hundred
lines of code that process these requests depending on a function/method name that has to send along with the request, 
so I started to think of ways to clean up this file to improve the readability and maintainability of the code.

## Contribution

All types of contribution are welcome, so do not hesitate to send a PR adding new feature or just fixing a typo.

## Thanks to

A special thanks to [JetBrains](https://www.jetbrains.com) company for their support to my OSS contributions.

<img width="130" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.png?_gl=1*1evhn6q*_ga*MzA3MTk5NzQ3LjE2MzU3OTk3MDA.*_ga_V0XZL7QHEB*MTYzNTg5MzE3NS4yLjEuMTYzNTg5MzkzNC4xNg..&_ga=2.162913596.1450626373.1635893177-307199747.1635799700"/>

## Support me

<a href="https://www.buymeacoffee.com/AmraniCh"><img width="500px" src="https://img.buymeacoffee.com/api/?url=aHR0cHM6Ly9jZG4uYnV5bWVhY29mZmVlLmNvbS91cGxvYWRzL3Byb2ZpbGVfcGljdHVyZXMvMjAyMS8xMC9jYWYzNWY4MjgzNGRjMTBhNWEyMzhmN2MwNDJlODJhMy5qcGdAMzAwd18wZS53ZWJw&creator=AmraniCh&is_creating=a%20PHP/JavaScript%20Engineer,%20Open%20Source%20Enthusiast&design_code=1&design_color=%23BD5FFF&slug=AmraniCh"/></a>

## LICENSE

The library is licensed under the open
source [MIT licence](https://github.com/AmraniCh/ajax-dispatcher/blob/master/LICENSE).
