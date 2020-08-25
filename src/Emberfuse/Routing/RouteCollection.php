<?php

namespace Emberfuse\Routing;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Emberfuse\Routing\Contracts\RouteCollectionInterface;

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
