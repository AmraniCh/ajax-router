<?php

namespace AmraniCh\AjaxDispatcher;

use AmraniCh\AjaxDispatcher\Http\Response;

/**
 * AmraniCh\AjaxDispatcher\Dispatcher
 *
 * Uses the router to generate the actual response for the AJAX request.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class Dispatcher
{
    /** @var Router */
    protected $router;

    /** @var callable */
    protected $beforeCallback;

    /** @var callable */
    protected $onExceptionCallback;

    /** @var bool */
    protected $cleanBuffer = false;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->setRouter($router);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return callable
     */
    public function getBeforeCallback()
    {
        return $this->beforeCallback;
    }

    /**
     * @return callable
     */
    public function getOnExceptionCallback()
    {
        return $this->onExceptionCallback;
    }

    /**
     * @param Router $router
     *
     * @return Dispatcher
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Dispatches router request to an appropriate handler and execute it to generate the response for the current AJAX
     * request.
     *
     * @return Dispatcher
     * @throws \Exception
     */
    public function dispatch()
    {
        if ($this->beforeCallback) {
            $requestVars = $this
                ->getRouter()
                ->getRequest()
                ->getVariables();
                
            if ($this->handleException(function() use ($requestVars) {
                    return call_user_func($this->beforeCallback, (object)$requestVars);
                }) === false) {
                return null;
            }
        }

        $handler  = $this->router->run();

        $response = $this->handleException($handler);

        if (!$response instanceof Response) {
            $response = Response::raw($response);
        }

        if ($this->cleanBuffer && $this->getBufferLevels() > 0) {
            $this->eraseBuffer();
        }

        $response->send();

        return $this;
    }

    /**
     * Executes some code before dispatching the current request.
     *
     * @param callable $callable The callback function to be executed, all the request parameters will be passed to
     *                           this callback as a first argument.
     *
     * @return Dispatcher
     */
    public function before(callable $callable)
    {
        $this->beforeCallback = $callable;

        return $this;
    }

    /**
     * Allows to use a custom exception handler for exceptions may throw when calling the handlers.
     *
     * @param callable $callable a callback function that will accept the exception as a first argument.
     *
     * @return Dispatcher
     */
    public function onException(callable $callable)
    {
        $this->onExceptionCallback = $callable;

        return $this;
    }

    /**
     * Tells the dispatcher to exit the script directly after executing the AJAX handler.
     *
     * @return void
     */
    public function stop()
    {
        exit();
    }

    /**
     * Tells the dispatcher to clean the output buffer (if is active) before dispatching the request.
     *
     * @return Dispatcher
     */
    public function cleanBuffer()
    {
        $this->cleanBuffer = true;

        return $this;
    }

    /**
     * @return bool
     */
    protected function eraseBuffer()
    {
        return ob_clean();
    }

    /**
     * @return int
     */
    protected function getBufferLevels()
    {
        return ob_get_level();
    }

    /**
     * handle exceptions that may throw during the callback call.
     *
     * @param \Closure $callback
     *
     * @return mixed
     * @throws \Exception
     */
    protected function handleException(\Closure $callback)
    {
        try {
            return $callback();
        } catch (\Exception $ex) {
            if (is_callable($this->onExceptionCallback)) {
                call_user_func($this->onExceptionCallback, $ex);
                return false;
            }

            $class = get_class($ex);
            throw new $class($ex->getMessage());
        }
    }
}
