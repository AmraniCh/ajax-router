<?php

namespace AmraniCh\AjaxDispatcher;

use AmraniCh\AjaxDispatcher\Exception\AjaxDispatcherException;
use AmraniCh\AjaxDispatcher\Exception\BadRequestException;
use AmraniCh\AjaxDispatcher\Exception\HandlerNotFoundException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Exception\MethodNotAllowedException;
use AmraniCh\AjaxDispatcher\Handler\HandlerCollection;
use AmraniCh\AjaxDispatcher\Http\Request;

/**
 * AmraniCh\AjaxDispatcher\Router
 *
 * Find the proper handler for an AJAX request.
 *
 * @since  1.0.0
 * @author El Amrani Chakir <contact@amranich.dev>
 * @link   https://amranich.dev
 */
class Router
{
    /** @var Request */
    protected $request;

    /** @var string */
    protected $handlerName;

    /** @var HandlerCollection */
    protected $handlerCollection;

    /** @var array */
    protected $controllers = [];

    /**
     * @param Request           $request     An instance of {@see \AmraniCh\AjaxDispatcher\Request} that represent the
     *                                       current AJAX request.
     * @param string            $handlerName The handler name to be executed.
     * @param HandlerCollection $handlers    An associative array that register request handlers.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Request $request, $handlerName, HandlerCollection $handlers)
    {
        $this->setRequest($request);
        $this->setHandlerName($handlerName);
        $this->setHandlerCollection($handlers);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getHandlerName()
    {
        return $this->handlerName;
    }

    /**
     * @return HandlerCollection
     */
    public function getHandlerCollection()
    {
        return $this->handlerCollection;
    }

    /**
     * Gets registered controllers namespaces.
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * @param Request $request
     *
     * @return Router
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param string $handlerName
     *
     * @return Router
     * @throws InvalidArgumentException
     */
    public function setHandlerName($handlerName)
    {
        if (!is_string($handlerName)) {
            throw new InvalidArgumentException(sprintf(
                "An AJAX handler name must be of type string, '%s' type given.",
                gettype($handlerName)
            ));
        }

        $this->handlerName = $handlerName;

        return $this;
    }

    /**
     * @param HandlerCollection $handlerCollection
     *
     * @return Router
     */
    public function setHandlerCollection($handlerCollection)
    {
        $this->handlerCollection = $handlerCollection;

        return $this;
    }

    /**
     * Runs the logic of find the proper handler for the request.
     *
     * @return \Closure
     * @throws AjaxDispatcherException
     * @internal
     */
    public function run()
    {
        if (!array_key_exists($this->handlerName, $this->request->getVariables())) {
            throw new BadRequestException(sprintf(
                "the handler name '%s' not found in request variables.",
                $this->handlerName
            ));
        }

        foreach ($this->handlerCollection->getHandlers() as $handler) {
            if ($handler->getName() !== $this->request->getVariables()[$this->handlerName]) {
                continue;
            }

            if (!in_array($this->request->getMethod(), $handler->getMethods())) {
                throw new MethodNotAllowedException(sprintf(
                    "The handler '%s' is registered for another HTTP request method(s) [%s].",
                    $handler->getName(),
                    implode(', ', $handler->getMethods())
                    ), 405, $handler->getMethods());
            }

            return $this
                ->createHandlerResolverClass()
                ->resolve($handler);
        }

        throw new HandlerNotFoundException('No handler found for this AJAX request.');
    }

    /**
     * Register controllers names.
     *
     * @param array $controllers An array of controller instances or namespaces.
     *
     * @return Router
     */
    public function registerControllers(array $controllers)
    {
        $this->controllers = $controllers;

        return $this;
    }

    /**
     * @return HandlerResolver
     */
    protected function createHandlerResolverClass()
    {
        return new HandlerResolver(
            $this->getRequest(),
            $this->getControllers()
        );
    }
}
