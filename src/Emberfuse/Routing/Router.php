<?php

namespace Emberfuse\Routing;

use Emberfuse\Container\Container;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Emberfuse\Routing\Contracts\RouterInterface;
use Emberfuse\Routing\Contracts\RouteCollectionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router implements RouterInterface
{
    /**
     * All registered routes.
     *
     * @var \Emberfuse\Routing\Contracts\RouteCollectionInterface
     */
    protected $routes;

    /**
     * Instance of service container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Instance of currently serving request.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $currentRequest;

    /**
     * Create a new Router instance.
     *
     * @param \Psr\Container\ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->routes = new RouteCollection();
        $this->container = $container ?: new Container();
    }

    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Emberfuse\Routing\Route
     */
    public function get(string $uri, string $action): Route
    {
        return $this->addRoute($uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param string $action
     *
     * @return \Emberfuse\Routing\Route
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
     * @return \Emberfuse\Routing\Route
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
     * @return \Emberfuse\Routing\Route
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
     * @return \Emberfuse\Routing\Route
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
     * @return \Emberfuse\Routing\Route
     */
    public function options(string $uri, string $action): Route
    {
    }

    protected function addRoute(string $uri, string $action): Route
    {
        return $this->getRouteCollection()->add(
            $this->createRoute($uri, $action)
        );
    }

    protected function createRoute(string $uri, string $action)
    {
        $route = new Route($uri, 'GET', $action);

        $route->setRouter($this)->setContianer($this->container);

        return $route;
    }

    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;

        return $this->dispatchToRoute($request);
    }

    public function dispatchToRoute(Request $request)
    {
        return $this->runRoute($request, $this->findRoute($request));
    }

    public function findRoute($request)
    {
        try {
            $route = $this->routes->match($request);

            $this->container->instance(Route::class, $route);

            return $route;
        } catch (RouteNotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    protected function runRoute(Request $request, Route $route)
    {
        return $this->prepareResponse($request, $route->run());
    }

    public function prepareResponse(Request $request, $response)
    {
        return static::toResponse($request, $response);
    }

    public static function toResponse(Request $request, $response)
    {
        $response = new Response($response);

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->prepare($request);
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
