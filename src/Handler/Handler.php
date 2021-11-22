<?php

namespace AmraniCh\AjaxDispatcher\Handler;

use AmraniCh\AjaxDispatcher\Http\Request;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;

/**
 * AmraniCh\AjaxDispatcher\Handler\Handler
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class Handler
{
    /** @var array */
    protected $methods;

    /** @var string */
    protected $name;

    /** @var string|callable */
    protected $value;

    /**
     * HTTP methods supported by a handler.
     *
     * @var array
     */
    const SUPPORTED_METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
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
     * Gets handler methods.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Gets handler name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets handler value.
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
     * @return Handler
     * @throws InvalidArgumentException
     */
    public function setMethods(array $methods)
    {
        foreach ($methods as $method) {
            if (!in_array($method, self::SUPPORTED_METHODS)) {
                throw new InvalidArgumentException(sprintf(
                    "An AjaxDispatcher handler not support the '%s' HTTP request method, the supported methods are [%s].",
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
     * @return Handler
     *
     * @throws InvalidArgumentException
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException("The handler name must be of type string.");
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of value
     *
     * @return Handler
     *
     * @throws InvalidArgumentException
     */
    public function setValue($value)
    {
        $type = gettype($value);

        if ($type !== 'string' && !is_callable($value)) {
            throw new InvalidArgumentException(sprintf(
                "A handler value type must be either a string/callable, giving '%s' for handler with name '%s'",
                $type,
                $this->getName()
            ));
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Gets handler value type.
     * 
     * @return string
     */
    public function getType()
    {
        if (is_callable($this->value)) {
            return 'callable';
        }

        return gettype($this->value);
    }

    /**
     * Creates handler with GET request method.
     *
     * @param string          $name
     * @param string|callable $value
     * 
     * @return Handler
     */
    public static function get($name, $value)
    {
        return static::getInstance(['GET'], $name, $value);
    }

    /**
     * Creates handler with POST request method.
     *
     * @param string          $name
     * @param string|callable $value
     * 
     * @return Handler
     */
    public static function post($name, $value)
    {
        return static::getInstance(['POST'], $name, $value);
    }

    /**
     * Creates handler with PUT request method.
     *
     * @param string          $name
     * @param string|callable $value
     *
     * @return Handler
     * @throws InvalidArgumentException
     */
    public static function put($name, $value)
    {
        return static::getInstance(['PUT'], $name, $value);
    }

    /**
     * Creates handler with DELETE request method.
     *
     * @param string          $name
     * @param string|callable $value
     * 
     * @return Handler
     */
    public static function delete($name, $value)
    {
        return static::getInstance(['DELETE'], $name, $value);
    }

    /**
     * Creates handler with multiple request methods.
     * 
     * @param array           $methods
     * @param string          $name
     * @param string|callable $value
     * 
     * @return Handler
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
     * @return Handler
     * @throws InvalidArgumentException
     */
    protected static function getInstance($methods, $name, $value)
    {
        return new static($methods, $name, $value);
    }
}
