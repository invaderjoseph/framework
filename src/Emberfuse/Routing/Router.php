<?php

namespace Emberfuse\Routing;

use Emberfuse\Container\Container;
use Psr\Container\ContainerInterface;
use Emberfuse\Routing\Contracts\RouterInterface;
use Emberfuse\Routing\Contracts\RouteCollectionInterface;

class Router implements RouterInterface
{
    /**
     * All registered routes.
     *
     * @var \Emberfuse\Routing\Contracts\RouterInterface
     */
    protected $routes;

    /**
     * Create new emberfuse router instance.
     *
     * @param \Psr\Container\ContainerInterface|null     $container
     * @param \Psr\Routing\RouteCollectionInterface|null $container
     *
     * @return void
     */
    public function __construct(?ContainerInterface $container = null, ?RouteCollectionInterface $routes = null)
    {
        $this->container = $container ?: new Container();
        $this->routes = $routes ?: new RouteCollection();
    }

    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function get(string $uri, string $action): Route
    {
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function post(string $uri, string $action): Route
    {
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function put(string $uri, string $action): Route
    {
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function patch(string $uri, string $action): Route
    {
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function delete(string $uri, string $action): Route
    {
    }

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function options(string $uri, string $action): Route
    {
    }

    /**
     * Register/add a given route to collection.
     *
     * @param string $method
     * @param string $uri
     * @param string $action
     *
     * @return \Emberfuse\Routing\Route
     */
    public function addRoute(string $method, string $uri, string $action): Route
    {
        return $this->routes->add($this->createRoute($method, $uri, $action));
    }

    /**
     * Create new emberfuse route instance.
     *
     * @param string $method
     * @param string $uri
     * @param string $action
     *
     * @return \Emberfuse\Routing\Route
     */
    protected function createRoute(string $method, string $uri, string $action): Route
    {
        $route = new Route($method, $uri, $action);

        $route->setRouter($this)
            ->setContainer($this->container)
            ->compile();

        return $route;
    }

    /**
     * Get all registered routes.
     *
     * @return \Emberfuse\Routing\Contracts\RouteCollectionInterface
     */
    public function getRouteCollection(): RouteCollectionInterface
    {
        return $this->routes;
    }
}
