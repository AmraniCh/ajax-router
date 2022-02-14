<?php

namespace AmraniCh\AjaxRouter;

use Psr\Http\Message\ServerRequestInterface;
use AmraniCh\AjaxRouter\Psr7\PSR7RequestAdapter;
use AmraniCh\AjaxRouter\Exception\LogicException;
use AmraniCh\AjaxRouter\RouteResolver\RouteResolver;
use AmraniCh\AjaxRouter\Exception\BadRequestException;
use AmraniCh\AjaxRouter\Exception\RouteNotFoundException;
use AmraniCh\AjaxRouter\Exception\InvalidArgumentException;
use AmraniCh\AjaxRouter\Exception\UnexpectedValueException;
use AmraniCh\AjaxRouter\Exception\MethodNotAllowedException;

/**
 * AmraniCh\AjaxRouter\Router
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
    protected $routeVariable;

    /** @var array */
    protected $routes;

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
    public function getRouteVariable()
    {
        return $this->routeVariable;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
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
     * @param string $routeVariable
     *
     * @return Router
     * @throws InvalidArgumentException
     */
    public function setRouteVariable($routeVariable)
    {
        if (!is_string($routeVariable)) {
            throw new InvalidArgumentException(sprintf(
                "A route variable must be of type string, '%s' type given.",
                gettype($routeVariable)
            ));
        }

        $this->routeVariable = $routeVariable;

        return $this;
    }

    /**
     * @param array $routes
     *
     * @return Router
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Register controllers namespaces/instances.
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
     * Runs the logic of find the proper handler for the request.
     *
     * @return \Closure
     * @throws BadRequestException|RouteNotFoundException|MethodNotAllowedException|UnexpectedValueException|LogicException
     * @internal
     */
    public function run()
    {
        $request = new PSR7RequestAdapter($this->request);
        $variables = $request->getVariables();

        if (!array_key_exists($this->routeVariable, $variables)) {
            throw new BadRequestException(sprintf(
                "The route parameter '%s' not found in request variables.",
                $this->routeVariable
            ));
        }

        $routeName = $variables[$this->routeVariable];

        if ($routeName === '') {
            throw new BadRequestException("Route name not given.");
        }

        foreach ($this->getRoutes() as $route) {
            if ($route->getName() !== $routeName) {
                continue;
            }

            if (!in_array($this->request->getMethod(), $route->getMethods())) {
                throw new MethodNotAllowedException(sprintf(
                    "The handler '%s' is registered for another HTTP request method(s) [%s].",
                    $route->getName(),
                    implode(', ', $route->getMethods())
                ), 405, $route->getMethods());
            }

            $resolver = new RouteResolver(
                $this->getRequest(),
                $variables,
                $this->getControllers()
            );

            return $resolver->resolve($route);
        }

        throw new RouteNotFoundException('No Route found for this request.');
    }
}
