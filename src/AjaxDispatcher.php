<?php

namespace AmraniCh;

class AjaxDispatcher
{
    /** @var array */
    protected $handlers;

    /** @var array */
    protected $context;

    /** @var callable */
    protected $beforeCallback;

    /** @var callable */
    protected $onExceptionCallback;

    /** @var array */
    protected $controllers = [];

    /** @var array */
    protected $HTTPMethods
        = [
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'PATCH'
        ];

    /** @var string */
    protected $key;

    /** @var string */
    protected $requestMethod;

    /**
     * AjaxDispatcher Constructor.
     *
     * @param string $key
     * @param array $handlers
     */
    public function __construct(array $server, string $key, array $handlers)
    {
        $this->server   = $server;
        $this->key      = $key;
        $this->handlers = $handlers;
    }

    /**
     * Start dispatching the current AJAX request to the appropriate
     * handler (controller method or a callback).
     *
     * @return void
     * @throws \Throwable
     */
    public function dispatch(): void
    {
        $this->checkScriptContext();

        $this->validateHandlers($this->handlers);

        $this->requestMethod = $this->server['REQUEST_METHOD'];

        $this->context = $this->getRequestVariables($this->requestMethod);

        if (is_callable($this->beforeCallback)) {
            if ($this->handleException(function () {
                    return call_user_func($this->beforeCallback, (object)$this->context);
                }) === false) {
                return;
            }
        }

        if (!array_key_exists($this->key, $this->context)) {
            throw new AjaxDispatcherException("the key '$this->key' not found in request variables.");
        }

        $this->handle();
    }

    /**
     * Register controllers instances and namespaces.
     *
     * @param array $controllers
     * @return AjaxDispatcher
     */
    public function registerControllers(array $controllers): AjaxDispatcher
    {
        foreach ($controllers as $controller) {
            if (is_string($controller)) {
                $this->controllers[] = new $controller();
                continue;
            }
            $this->controllers[] = $controller;
        }

        return $this;
    }

    /**
     * Executes some code before dispatching the current request.
     *
     * @param callable $callback
     * @return AjaxDispatcher
     */
    public function before(callable $callback): AjaxDispatcher
    {
        $this->beforeCallback = $callback;
        return $this;
    }

    /**
     * Handle exceptions that may occur during the flow of the current AJAX request.
     *
     * @param callable $callback
     * @return AjaxDispatcher
     */
    public function onException(callable $callback): AjaxDispatcher
    {
        $this->onExceptionCallback = $callback;
        return $this;
    }

    /**
     * Checks if the current script was executed via an HTTP client
     * like a browser, if so check the HTTP request is issued using
     * the XMLHTTPRequest in the client side.
     *
     * @return void
     * @throws AjaxDispatcherException
     */
    protected function checkScriptContext(): void
    {
        if (!function_exists('getallheaders')) {
            throw new AjaxDispatcherException('AjaxDispatcher works only within an HTTP request context '
                . '(request that issued by a HTTP client like a browser).');
        }

        $headers = getallheaders();

        if ($headers === false) {
            throw new AjaxDispatcherException(sprintf(
                'An error occur when trying retrieving the current HTTP request headers : %s',
                error_get_last()['message']
            ));
        }

        if (array_key_exists('X-Requested-With', $headers)
            && $headers['X-Requested-With'] === 'XMLHttpRequest') {
            throw new AjaxDispatcherException('AjaxDispatcher Accept only an AJAX requests.');
        }
    }

    /**
     * Validate the giving handlers array.
     *
     * @param array $handlers
     * @return void
     * @throws AjaxDispatcherException
     */
    protected function validateHandlers(array $handlers): void
    {
        foreach ($handlers as $method => $_handlers) {
            if (!in_array($method, $this->HTTPMethods)) {
                throw new AjaxDispatcherException("$method is not supported HTTP request method.");
            }

            foreach ($_handlers as $name => $handler) {
                $handlerkey = gettype($handler);
                if (in_array($handlerkey, ['string', 'array']) || is_callable($handler)) {
                    continue;
                }

                throw new AjaxDispatcherException("the type of '$name' handler value must be either a"
                    . " string/array/callable.");
            }
        }
    }

    /**
     * @throws AjaxDispatcherException
     */
    protected function handle()
    {
        foreach ($this->handlers[$this->requestMethod] as $name => $handler) {
            if ($name !== $this->context[$this->key]) {
                continue;
            }

            if (is_string($handler)) {
                echo($this->handleString($handler));
                return;
            }

            if (is_array($handler)) {
                echo($this->handleArray($handler));
                return;
            }

            if (is_callable($handler)) {
                echo($this->handleCallback($handler));
                return;
            }
        }

        throw new AjaxDispatcherException('No handler was found for this AJAX request.');
    }

    /**
     * Handles handlers that defined as a string.
     *
     * @param string $string
     * @return mixed
     */
    protected function handleString(string $string)
    {
        return call_user_func($this->getCallableMethod($string));
    }

    /**
     * Handles handlers that defined as an array.
     *
     * @param string $string
     * @return mixed
     */
    protected function handleArray(array $array)
    {
        $args = [];

        foreach (array_splice($array, 1) as $arg) {
            if (!array_key_exists($arg, $this->context)) {
                throw new AjaxDispatcherException("$arg is not exist in the request variables.");
            }
            $args[] = $this->context[$arg];
        }

        return call_user_func($this->getCallableMethod($array[0]), $args);
    }

    /**
     * Handles handlers that defined as a callabak functions.
     *
     * @param callable $callback
     * @return mixed
     */
    protected function handleCallback($callback)
    {
        $this->handleException(function () use ($callback) {
            $params = array_splice($this->context, 1);
            return call_user_func($callback, ...$params);
        });
    }

    /**
     * Extract the controller and method from
     * the giving string and return the callable
     * method from the controller object.
     *
     * @param string $string
     * @return callable|false
     * @throws AjaxDispatcherException
     */
    protected function getCallableMethod(string $string)
    {
        $tokens = @explode('@', $string);

        $controllerName = $tokens[0];
        $methodName     = $tokens[1];

        if (!$controller = $this->getControllerByName(($controllerName))) {
            throw new AjaxDispatcherException("Controller class '$controllerName' not found.");
        }

        if (!method_exists($controller, $methodName)) {
            throw new AjaxDispatcherException("Controller method '$methodName' not exist in"
                . " controller '$controllerName'.");
        }

        return function ($args = []) use ($controller, $methodName) {
            return $this->handleException(function () use ($controller, $methodName, $args) {
                return call_user_func_array([$controller, $methodName], $args);
            });
        };
    }

    /**
     * Get a controller instance from the registered controllers by its name.
     *
     * @param string $name
     * @return string|false
     */
    protected function getControllerByName(string $name)
    {
        foreach ($this->controllers as $controller) {
            $path = explode('\\', get_class($controller));
            if (array_pop($path) === $name) {
                return $controller;
            }
        }

        return false;
    }

    /**
     * Gets the current request variables.
     *
     * @param string $requestMethod
     * @return array
     */
    protected function getRequestVariables(string $requestMethod): array
    {
        switch ($requestMethod) {
            case 'GET':
                return $_GET;
                break;

            case 'POST':
                return $_POST;
                break;

            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                // TODO
                break;

            default:
                throw new AjaxDispatcherException("Unknown HTTP request method '$requestMethod'.");
        }
    }

    /**
     * handle exceptions that may throw during the callback call.
     *
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    protected function handleException($callback)
    {
        try {
            return $callback();
        } catch (\Throwable $ex) {
            if (is_callable($this->onExceptionCallback)) {
                call_user_func($this->onExceptionCallback, $ex);
                return false;
            }

            $class = get_class($ex);
            throw new $class($ex->getMessage());
        }
    }
}