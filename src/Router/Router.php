<?php

namespace AmraniCh\AjaxDispatcher\Router;

use AmraniCh\AjaxDispatcher\Exception\AjaxDispatcherException;
use AmraniCh\AjaxDispatcher\Exception\BadRequestException;
use AmraniCh\AjaxDispatcher\Exception\HandlerNotFoundException;
use AmraniCh\AjaxDispatcher\Exception\InvalidArgumentException;
use AmraniCh\AjaxDispatcher\Exception\MethodNotAllowedException;
use AmraniCh\AjaxDispatcher\Internal\PSR7RequestAdapter;
use Psr\Http\Message\ServerRequestInterface;

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
    /** @var ServerRequestInterface */
    protected $request;

    /** @var string */
    protected $handlerName;

    /** @var array */
    protected $handlers;

    /** @var array */
    protected $controllers = [];

    /**
     * @param ServerRequestInterface $request     A request class that implement the {@see ServerRequestInterface}
     *                                            interface.
     * @param string                 $handlerName The handler name to be executed.
     * @param array                  $handlers    An associative array that register request handlers.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ServerRequestInterface $request, $handlerName, array $handlers)
    {
        $this->setRequest($request);
        $this->setHandlerName($handlerName);
        $this->setHandlers($handlers);
    }

    /**
     * @return ServerRequestInterface
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
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
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
     * @param ServerRequestInterface $request
     *
     * @return Router
     */
    public function setRequest(ServerRequestInterface $request)
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
     * @param array $handlers
     *
     * @return Router
     */
    public function setHandlers($handlers)
    {
        $this->handlers = $handlers;

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
        $request = new PSR7RequestAdapter($this->request);
        $variables = $request->getVariables();

        if (!array_key_exists($this->handlerName, $variables)) {
            throw new BadRequestException(sprintf(
                "the handler name '%s' not found in request variables.",
                $this->handlerName
            ));
        }

        foreach ($this->getHandlers() as $handler) {
            if ($handler->getName() !== $variables[$this->handlerName]) {
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
                ->createHandlerResolverClass($variables)
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
    protected function createHandlerResolverClass($variables)
    {
        return new HandlerResolver(
            $this->getRequest(),
            $variables,
            $this->getControllers()
        );
    }
}
