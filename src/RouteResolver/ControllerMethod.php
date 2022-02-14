<?php

namespace AmraniCh\AjaxRouter\RouteResolver;

use AmraniCh\AjaxRouter\Exception\LogicException;

/**
 * AmraniCh\AjaxRouter\RouteResolver\ControllerMethod
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 * @internal
 */
class ControllerMethod
{
    /** @var string|object */
    protected $class;

    /** @var string */
    protected $name;

    /**
     * @param string|object $class
     * @param string $name
     */
    public function __construct($class, $name)
    {
        $this->setClass($class);
        $this->setName($name);
    }

    /**
     * @return object|string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param object|string $class
     *
     * @return ControllerMethod
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ControllerMethod
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Calls and executes the controller method.
     *
     * @param array $args
     *
     * @return mixed
     * @throws LogicException
     */
    public function call(array $args)
    {
        if (is_object($this->class) === false && class_exists($this->class) === false) {
            throw new LogicException("Controller class '$this->class' not found.");
        }

        if (!method_exists($this->class, $this->name)) {
            throw new LogicException(sprintf(
                    "The method '%s' not exist in controller '%s'.",
                    $this->name,
                    is_object($this->class) ? get_class($this->class) : $this->class
                )
            );
        }

        $callable = [
            is_string($this->class) ? new $this->class() : $this->class,
            $this->name
        ];

        return call_user_func_array($callable, $args);
    }
}
