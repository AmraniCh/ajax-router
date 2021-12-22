<?php

namespace AmraniCh\AjaxDispatcher;

use AmraniCh\AjaxDispatcher\Router\Router;
use Psr\Http\Message\ResponseInterface;

/**
 * AmraniCh\AjaxDispatcher\Dispatcher
 *
 * Generate the actual response for the AJAX request.
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
    protected $onExceptionCallback;

    /** @var bool */
    protected $cleanBuffer = false;

    /** @var PSR7ResponseSender */
    protected $sender;

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
        $handler = $this->router->run();

        $response = $this->handleException($handler);

        if ($this->cleanBuffer && ob_get_level() > 0) {
            ob_clean();
        }

        $sender = new PSR7ResponseSender($response);

        if ($response instanceof ResponseInterface) {
            $sender->send();
        }

        if (is_string($response)) {
            echo $response;
        }

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
     * handle exceptions that may thrown during the callback call.
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
