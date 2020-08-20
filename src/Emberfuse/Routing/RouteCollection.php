<?php

namespace Emberfuse\Routing;

use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Contracts\RouteCollectionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RouteCollection extends AbstractRouteCollection implements RouteCollectionInterface
{
    /**
     * List of routes with method as index.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * A flattened list of all registered routes.
     *
     * @var array
     */
    protected $routesList = [];

    /**
     * Add route to routes collections.
     *
     * @param \Emberfuse\ROuting\Route $route
     */
    public function add(Route $route): Route
    {
        $this->addToCollections($route);

        return $route;
    }

    /**
     * Add given route instance to all set collections.
     *
     * @param \Emberfuse\ROuting\Route $route
     *
     * @return void
     */
    protected function addToCollections(Route $route): void
    {
        $this->routes[$route->method()][$route->uri()] = $route;

        $this->routesList["{$route->method()}-{$route->uri()}"] = $route;
    }

    /**
     * Find requested route binding using given request instance.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Emberfuse\ROuting\Route
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function match(Request $request)
    {
        $routes = $this->routes[$request->getMethod()];

        foreach ($routes as $route) {
            if ($route->matches($request)) {
                return $route;
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Get all registered routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get full list of registered routes.
     *
     * @return array
     */
    public function getRoutesList(): array
    {
        return $this->routesList;
    }
}
