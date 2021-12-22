<?php

namespace AmraniCh\AjaxDispatcher\Router;

use AmraniCh\AjaxDispatcher\Exception\LogicException;
use AmraniCh\AjaxDispatcher\Exception\UnexpectedValueException;
use AmraniCh\AjaxDispatcher\Handler\Handler;
use AmraniCh\AjaxDispatcher\Internal\ControllerMethod;
use Psr\Http\Message\ServerRequestInterface;

/**
 * AmraniCh\AjaxDispatcher\HandlerResolver
 *
 * Resolve handlers values.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class HandlerResolver
{
    /** @var ServerRequestInterface */
    protected $request;

    /** @var array */
    protected $variables;

    /** @var array */
    protected $controllers;

    /**
     * @param ServerRequestInterface $request
     * @param array                  $variables
     * @param array                  $controllers
     */
    public function __construct(ServerRequestInterface $request, array $variables, array $controllers)
    {
        $this->request = $request;
        $this->variables = $variables;
        $this->controllers = $controllers;
    }

    /**
     * @param Handler $handler
     *
     * @return \Closure
     * @throws UnexpectedValueException
     */
    public function resolve(Handler $handler)
    {
        $value = $handler->getValue();
        $type = $handler->getType();

        if ($type === 'string') {
            return $this->resolveString($value);
        }

        if ($type === 'callable') {
            return $this->resolveCallable($value);
        }

        throw new UnexpectedValueException(sprintf(
            "Unexpected handler value, expecting string/callable '%s' given.",
            gettype($handler)
        ));
    }

    /**
     * Handles handlers that defined as a string.
     *
     * @param string $string
     *
     * @return \Closure
     */
    protected function resolveString($string)
    {
        return function () use ($string) {
            return call_user_func($this->getCallableMethod($string), [$this->variables, $this->request]);
        };
    }

    /**
     * Handles handlers that defined as a callback functions.
     *
     * @param callable $callback
     *
     * @return \Closure
     */
    protected function resolveCallable($callback)
    {
        return function () use ($callback) {
            return call_user_func($callback, $this->variables, $this->request);
        };
    }

    /**
     * Extract the controller and method from the giving string and return the callable method from the controller
     * object.
     *
     * @param string $string
     *
     * @return \Closure
     * @throws LogicException
     */
    protected function getCallableMethod($string)
    {
        $tokens = @explode('@', $string);

        $controllerName = $tokens[0];
        $controller = $this->getRegisteredControllerByName($controllerName);

        if (is_null($controller)) {
            throw new LogicException("Controller '$controllerName' not registered.");
        }

        $methodName = $tokens[1];
        $method = new ControllerMethod($controller, $methodName);

        return function ($args = []) use ($method) {
            return $method->call($args);
        };
    }

    /**
     * Gets controller instance from the registered controllers using it name.
     *
     * @param string $name
     *
     * @return string|null
     */
    protected function getRegisteredControllerByName($name)
    {
        foreach ($this->controllers as $controller) {
            if ($this->getControllerName($controller) === $name) {
                return $controller;
            }
        }

        return null;
    }

    /**
     * Gets controller name.
     *
     * @param object|string $controller
     *
     * @return string|null
     */
    protected function getControllerName($controller)
    {
        if (is_object($controller)) {
            $class = get_class($controller);
            $path = explode('\\', $class);
        }

        if (is_string($controller)) {
            $path = explode('\\', $controller);
        }

        return array_pop($path);
    }
}
