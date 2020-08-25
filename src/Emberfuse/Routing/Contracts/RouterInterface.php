<?php

namespace Emberfuse\Routing\Contracts;

use Emberfuse\Routing\Route;

interface RouterInterface
{
    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function get(string $uri, string $action): Route;

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function post(string $uri, string $action): Route;

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function put(string $uri, string $action): Route;

    /**
     * Register a new PATCH route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function patch(string $uri, string $action): Route;

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function delete(string $uri, string $action): Route;

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function options(string $uri, string $action): Route;

    /**
     * Get all registered routes.
     *
     * @return \Emberfuse\Routing\Contracts\RouteCollectionInterface
     */
    public function getRouteCollection(): RouteCollectionInterface;
}
