<?php

namespace AmraniCh\AjaxRouter;

use Psr\Http\Message\ResponseInterface;
use AmraniCh\AjaxRouter\Psr7\PSR7ResponseSender;

/**
 * AmraniCh\AjaxRouter\Dispatcher
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
     * Executes the route action and generate the response.
     *
     * @return Dispatcher
     * @throws \Exception
     */
    public function dispatch()
    {
        $handler = $this->router->run();

        $response = $this->handleException($handler);

        if ($response instanceof ResponseInterface) {
            $sender = new PSR7ResponseSender($response);
            $sender->send();
        }

        if (is_string($response)) {
            echo $response;
        }

        return $this;
    }

    /**
     * Allows using a custom exception handler for exceptions that may throw when calling the route actions.
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
     * handle exceptions that may be thrown during the callback call.
     *
     * @param \Closure $callback
     *
     * @return mixed
     * @throws \Exception
     */
    protected function handleException(\Closure $callback)
    {
        if (!is_callable($this->onExceptionCallback)) {
            return $callback();
        }

        try {
            return $callback();
        } catch (\Exception $ex) {
            return call_user_func($this->onExceptionCallback, $ex);
        }
    }
}
