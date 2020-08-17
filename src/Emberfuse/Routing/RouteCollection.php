<?php

namespace Emberfuse\Routing;

use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Contracts\RouteCollectionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RouteCollection extends AbstractRouteCollection implements RouteCollectionInterface
{
    /**
     * An array of the routes keyed by method.
     *
     * @var array
     */
    protected $routes = [];

    public function add(Route $route)
    {
        $this->addToCollections($route);

        return $route;
    }

    protected function addToCollections(Route $route)
    {
        $this->routes[$route->method()][$route->uri()] = $route;
    }

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
}
