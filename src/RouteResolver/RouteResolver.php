<?php

namespace AmraniCh\AjaxRouter\RouteResolver;

use AmraniCh\AjaxRouter\Route;
use Psr\Http\Message\ServerRequestInterface;
use AmraniCh\AjaxRouter\Exception\LogicException;
use AmraniCh\AjaxRouter\Exception\UnexpectedValueException;

/**
 * AmraniCh\AjaxRouter\RouteResolver\RouteResolver
 *
 * Resolve routes.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class RouteResolver
{
    /** @var ServerRequestInterface */
    protected $request;

    /** @var array */
    protected $variables;

    /** @var array */
    protected $controllers;

    /**
     * @param ServerRequestInterface $request
     * @param array $variables
     * @param array $controllers
     */
    public function __construct(ServerRequestInterface $request, array $variables, array $controllers)
    {
        $this->request = $request;
        $this->variables = $variables;
        $this->controllers = $controllers;
    }

    /**
     * @param Route $route
     *
     * @return \Closure
     * @throws UnexpectedValueException|LogicException
     */
    public function resolve(Route $route)
    {
        $value = $route->getValue();
        $type = $route->getType();

        if ($type === 'string') {
            return $this->resolveString($value);
        }

        if ($type === 'callable') {
            return $this->resolveCallable($value);
        }

        throw new UnexpectedValueException(sprintf(
            "Unexpected handler value, expecting string/callable '%s' given.",
            gettype($route)
        ));
    }

    /**
     * Resolves routes values that defined as a string.
     *
     * @param string $string
     *
     * @return \Closure
     * @throws LogicException
     */
    protected function resolveString($string)
    {
        $method = $this->getCallableMethod($string);
        return function () use ($method, $string) {
            return call_user_func($method, [$this->variables, $this->request]);
        };
    }

    /**
     * Resolve routes values that defined as a callback functions.
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
     * Extract the controller and method from the giving string and return
     * the callable method from the controller object.
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
        $path = null;

        if (is_object($controller)) {
            $class = get_class($controller);
            $path = explode('\\', $class);
        }

        if (is_string($controller)) {
            $path = explode('\\', $controller);
        }

        return !$path ?: array_pop($path);
    }
}
