<?php

namespace AmraniCh\AjaxDispatcher\Internal;

use AmraniCh\AjaxDispatcher\Exception\LogicException;

/**
 * AmraniCh\AjaxDispatcher\Internal\ControllerMethod
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
     * @param string        $name
     */
    public function __construct($class, $name)
    {
        $this->class = $class;
        $this->name  = $name;
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
     * Calls and executes the actual controller method.
     *
     * @param array $args
     *
     * @return mixed
     * @throws LogicException
     */
    public function call(array $args)
    {
        if ($this->isObject($this->class) === false && $this->isClassExists($this->class) === false) {
            throw new LogicException("Controller class '$this->class' not found.");
        }

        if (!$this->isMethodExists($this->name)) {
            throw new LogicException(sprintf(
                    "The method '%s' not exist in controller '%s'.",
                    $this->name,
                    is_object($this->class) ? get_class($this->class) : $this->class
                )
            );
        }

        $instance = $this->getClassInstance();
        $callable = [$instance, $this->name];

        return $this->callUserFuncArray($callable, $args);
    }

    /**
     * @return object
     */
    protected function getClassInstance()
    {
        return new $this->class();
    }

    /**
     * @param callable $callable
     * @param array    $args
     *
     * @return mixed
     */
    protected function callUserFuncArray($callable, array $args)
    {
        return call_user_func_array($callable, $args);
    }

    /**
     * @param string|object $class
     *
     * @return bool
     */
    protected function isObject($class)
    {
        return is_object($class);
    }

    /**
     * @param string|object $class
     *
     * @return bool
     */
    protected function isClassExists($class)
    {
        return class_exists($class);
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    protected function isMethodExists($method)
    {
        return method_exists($this->class, $method);
    }
}
