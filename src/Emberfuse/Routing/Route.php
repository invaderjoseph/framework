<?php

namespace Emberfuse\Routing;

use Psr\Container\ContainerInterface;
use Emberfuse\Routing\Contracts\RouterInterface;

class Route
{
    /**
     * HTTP method type,.
     *
     * @var string
     */
    public $method;

    /**
     * URI string.
     *
     * @var string
     */
    public $uri;

    /**
     * Route response action.
     *
     * @var string
     */
    public $action;

    /**
     * Instance of Emberfuse router.
     *
     * @var \Emberfuse\Routing\Contracts\RouterInterface
     */
    protected $router;

    /**
     * Instance of Emberfuse container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Create new instance of Emberfuse route.
     *
     * @param string $method
     * @param string $uri
     * @param string $action
     *
     * @return void
     */
    public function __construct(string $method, string $uri, string $action)
    {
        $this->method = $method;
        $this->uri = $this->prefixUri($uri);
        $this->action = $this->parseAction($action);
    }

    /**
     * Prefix the given URI with the last prefix.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function prefixUri(string $uri): string
    {
        return trim('/' . trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Parse given action string to controller and method.
     *
     * @param string $action
     *
     * @return array
     */
    protected function parseAction(string $action): array
    {
        return (array) $action;
    }

    /**
     * Get route URI.
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get route HTTP method.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Set instance of router.
     *
     * @param \Emberfuse\Routing\Contracts\RouterInterface $router
     *
     * @return \Emberfuse\Routing\Route
     */
    public function setRouter(RouterInterface $router): Route
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Set service container instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Emberfuse\Routing\Route
     */
    public function setContainer(ContainerInterface $container): Route
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Compile given route instance.
     *
     * @return \Emberfuse\Routing\Route
     */
    public function compile(): Route
    {
        return $this;
    }
}
