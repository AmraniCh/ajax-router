<?php

namespace AmraniCh\AjaxRouter;

use AmraniCh\AjaxRouter\Exception\RoutesFileException;
use AmraniCh\AjaxRouter\Exception\InvalidArgumentException;

/**
 * AmraniCh\AjaxRouter\RouteResolver\RouteResolver
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class Route
{
    /** @var Route */
    protected static $currentRoute;

    /** @var array */
    protected $methods;

    /** @var string */
    protected $name;

    /** @var string|callable */
    protected $value;

    /**
     * HTTP methods supported by a route.
     *
     * @var array
     */
    const SUPPORTED_METHODS = [
        'GET',
        'POST',
    ];

    /**
     * @param array           $methods
     * @param string          $name
     * @param string|callable $value
     *
     * @throws InvalidArgumentException
     */
    public function __construct($methods, $name, $value)
    {
        $this->setMethods($methods);
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Gets route methods.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Gets route name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets route value.
     *
     * @return string|callable
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of method
     *
     * @param array $methods
     *
     * @return Route
     * @throws InvalidArgumentException
     */
    public function setMethods(array $methods)
    {
        foreach ($methods as $method) {
            if (!in_array($method, self::SUPPORTED_METHODS)) {
                throw new InvalidArgumentException(sprintf(
                    "'%s' HTTP request method not supported by a route, the supported methods are [%s].",
                    $method,
                    implode(', ', self::SUPPORTED_METHODS)
                 ));
            }
        }

        $this->methods = $methods;

        return $this;
    }

    /**
     * Set the value of name
     *
     * @return Route
     *
     * @throws InvalidArgumentException
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException("The route name must be of type string.");
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of value
     *
     * @return Route
     *
     * @throws InvalidArgumentException
     */
    public function setValue($value)
    {
        if (!is_string($value) && !is_array($value) && !is_callable($value)) {
            throw new InvalidArgumentException(sprintf(
                "A route value type must be either a string/array, giving '%s' for route with name '%s'",
                gettype($value),
                $this->getName()
            ));
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Define routes from the giving file content.
     *
     * @param string $path
     *
     * @return array
     * @throws RoutesFileException
     */
    public static function fromFile($path)
    {
        if (!file_exists($path)) {
            throw new RoutesFileException("The routes file ($path) not exists.");
        }

        if (!is_readable($path)) {
            throw new RoutesFileException("The routes file ($path) is not readable, please check the file permissions.");
        }

        $content = include($path);

        if (!is_array($content)) {
            throw new RoutesFileException(sprintf(
                "The routes file (%s) must return an array of routes objects, '%s' value returned.",
                $path,
                gettype($content)
            )); 
        }

        return $content;
    }

    /**
     * @param Route $route
     * 
     * @return void
     */
    public static function setCurrentRoute(Route $route)
    {
        self::$currentRoute = $route;
    }

    /**
     * @return Route
     */
    public static function getCurrentRoute()
    {
        return static::$currentRoute;
    }

    /**
     * Creates route with GET request method.
     *
     * @param string          $name
     * @param string|callable $value
     *
     * @return Route
     * @throws InvalidArgumentException
     */
    public static function get($name, $value)
    {
        return static::getInstance(['GET'], $name, $value);
    }

    /**
     * Creates route with POST request method.
     *
     * @param string          $name
     * @param string|callable $value
     *
     * @return Route
     * @throws InvalidArgumentException
     */
    public static function post($name, $value)
    {
        return static::getInstance(['POST'], $name, $value);
    }

    /**
     * Creates route with multiple request methods.
     *
     * @param array           $methods
     * @param string          $name
     * @param string|callable $value
     *
     * @return Route
     * @throws InvalidArgumentException
     */
    public static function many($methods, $name, $value)
    {
        return static::getInstance($methods, $name, $value);
    }

    /**
     * Instance factory.
     *
     * @param array           $methods
     * @param string          $name
     * @param string|callable $value
     *
     * @return Route
     * @throws InvalidArgumentException
     */
    protected static function getInstance($methods, $name, $value)
    {
        return new static($methods, $name, $value);
    }
}
