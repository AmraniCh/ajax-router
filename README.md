![[preview]](https://socialify.git.ci/AmraniCh/ajax-dispatcher/image?description=1&descriptionEditable=Handle%20AJAX%20requests%20and%20send%20them%20to%20an%20appropriate%20handler.%20Also%20provides%20helper%20classes%20to%20simulate%20HTTP%20requests%20and%20responses.&font=Raleway&owner=1&pattern=Charlie%20Brown&theme=Light)

[![packagist](https://img.shields.io/packagist/v/AmraniCh/ajax-dispatcher?include_prereleases)](https://packagist.org/packages/amranich/ajax-dispatcher)
[![tests](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml/badge.svg)](https://github.com/AmraniCh/ajax-dispatcher/actions/workflows/tests.yml)
[![coverage](https://img.shields.io/codecov/c/github/AmraniCh/ajax-dispatcher-clone?token=1N5E5MNV8M)](https://app.codecov.io/gh/AmraniCh/ajax-dispatcher)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/AmraniCh/ajax-dispatcher/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/AmraniCh/ajax-dispatcher/?branch=master)


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
    Response::json(['error' => $ex->getMessage()], $ex->getCode())->send();
    exit();
}
```

## Usage

### Route to controller/class method

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

### Catch handlers exceptions

*I want to catch exceptions that only occurs from my AJAX handlers, and not those thrown by the library or somewhere else, how I can
do that ?*

Answer :

```php
$dispatcher->onException(function (\Exception $ex) {
    // $ex exception thrown by a handler
});
```

### Clean output buffer before calling requests handlers (Recommended)

To be sure that the response (output) for the ajax request it comes only from the defined handlers, and prevent
unexpected echos from sending their content earlier to the browser use the `cleanBuffer` method :

```php
$dispatcher
    ->cleanBuffer()
    ->dispatch();
```

### Exit the script after dispatching the request (Recommended)

Use `stop()` to tell the dispatcher to exit the script right after the handler execution, this can be useful to ensure that the response (output)
generated by your handlers will not be altered or modified after :

```php
$dispatcher
    ->dispatch()
    ->stop();
```

<hr>

## Security tips for production servers

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
    || $request->getHeaderValue('HTTP_REFERER') !== 'https://www.yourdomain.com') {
    Response::raw('bad request.', 400)->send();
    exit();
}
```

**Note :** HTTP headers can be spoofed, that means the content of the `HTTP_PREFER` header cannot be trusted.

### 3. Using CRSF tokens

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

I already implemented all these security tips in one example, if you want to check it out see the [examples/production](examples/production) folder.

<hr>

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
