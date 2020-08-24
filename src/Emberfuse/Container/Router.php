<?php

namespace Emberfuse\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;
use Emberfuse\Routing\Contracts\RouterInterface;

class Router implements RouterInterface
{
    /**
     * Instance of service container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Create new router instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct(?ContainerInterface $container)
    {
        $this->container = $container;
        $this->routes = new RouteCollection();
    }
}
