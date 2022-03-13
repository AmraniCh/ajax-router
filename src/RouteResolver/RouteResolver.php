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

        if (is_string($value)) {
            return $this->resolveString($value);
        }

        if (is_array($value)) {
            return $this->resolveArray($value);
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
        $tokens = @explode('@', $string);
        $controller = $tokens[0];
        $method = $tokens[1];

        $registeredController = $this->getRegisteredControllerByName($controller);

        if (!$registeredController) {
            throw new LogicException("Controller class '$controller' not registered.");
        }

        $method = $this->getCallableMethod($registeredController, $method);

        return function () use ($method) {
            return call_user_func($method, [$this->variables, $this->request]);
        };
    }

    /**
     * Resolve routes values that defined as a callback functions.
     *
     * @param array $array
     *
     * @return \Closure
     */
    protected function resolveArray(array $array)
    {
        $method = $this->getCallableMethod(new $array[0], $array[1]);

        return function () use ($method) {
            return call_user_func($method, [$this->variables, $this->request]);
        };
    }

    /**
     * Extract the controller and method from the giving string and return
     * the callable method from the controller object.
     *
     * @param string $controller
     * @param string $method
     *
     * @return \Closure
     */
    protected function getCallableMethod($controller, $method)
    {
        $method = new ControllerMethod($controller, $method);

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
