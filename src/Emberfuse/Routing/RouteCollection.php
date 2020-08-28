<?php

namespace Emberfuse\Routing;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Symfony\Component\HttpFoundation\Request;
use Emberfuse\Routing\Contracts\RouteCollectionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RouteCollection implements RouteCollectionInterface, Countable, IteratorAggregate
{
    /**
     * Routes list seperated by method type.
     *
     * @var array
     */
    protected $routes;

    /**
     * Routes list seperated by method and uri combination.
     *
     * @var array
     */
    protected $allRoutes;

    /**
     * Add given route to collections.
     *
     * @param \Emberfuse\Routing\Route $route
     *
     * @return \Emberfuse\Routing\Route
     */
    public function add(Route $route): Route
    {
        $this->addToCollections($route);

        return $route;
    }

    /**
     * Add given route to all available collection lists.
     *
     * @param \Emberfuse\Routing\Route $route
     *
     * @return void
     */
    protected function addToCollections(Route $route): void
    {
        $this->routes[$route->method()][$route->uri()] = $route;

        $this->allRoutes[$route->method() . $route->uri()] = $route;
    }

    /**
     * Find the route matching a given request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Emberfse\Routing\Route
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function match(Request $request): Route
    {
        foreach ($this->routes[$request->getMethod()] as $route) {
            if ($route->matches($request)) {
                return $route->bind($request);
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
        return array_values($this->allRoutes);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getRoutes());
    }
}
